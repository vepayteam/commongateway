<?php

namespace app\modules\kfapi\controllers;

use app\models\antifraud\AntiFraudRefund;
use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\CardToken;
use app\models\kfapi\KfOut;
use app\models\kfapi\KfRequest;
use app\models\mfo\MfoTestError;
use app\models\payonline\Cards;
use app\models\Payschets;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\payment_strategies\mfo\MfoOutPayAccountStrategy;
use app\services\PaySchetService;
use Yii;
use yii\base\Exception;
use yii\helpers\VarDumper;
use yii\mutex\FileMutex;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class OutController extends Controller
{
    use CorsTrait;

    /**
     * @var PaySchetService
     */
    private $paySchetService;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->paySchetService = \Yii::$app->get(PaySchetService::class);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $this->updateBehaviorsCors($behaviors);
        return $behaviors;
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if ($this->checkBeforeAction()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;
            return parent::beforeAction($action);
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);

        try {
            Yii::info([
                'endpoint' => $action->uniqueId,
                'header' => Yii::$app->request->headers->toArray(),
                'body' => Yii::$app->request->post(),
                'return' => (array) $result,
            ], 'mfo_' . $action->controller->id . '_' . $action->id);
        } catch (\Exception $e) {
            Yii::error([$e->getMessage(), $e->getTrace(), $e->getFile(), $e->getLine()], 'kfapi_out');
        }

        return $result;
    }

    protected function verbs()
    {
        return [
            'ul' => ['POST'],
            'state' => ['POST'],
        ];
    }

    /**
     * Вывод на счет юрлица
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionUl()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $outPayAccountForm = new OutPayAccountForm();
        $outPayAccountForm->setScenario(OutPayAccountForm::SCENARIO_UL);
        $outPayAccountForm->load($kf->req, '');
        $outPayAccountForm->partner = $kf->partner;
        if (!$outPayAccountForm->validate()) {
            return ['status' => 0, 'message' => $outPayAccountForm->GetError()];
        }

        $mfoOutPayAccountStrategy = new MfoOutPayAccountStrategy($outPayAccountForm);

        try {
            /** @var PaySchet $paySchet */
            $paySchet = $mfoOutPayAccountStrategy->exec();
            return [
                'status' => 1,
                'id' => $paySchet->ID,
                'message' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Статус выплаты
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws ForbiddenHttpException
     */
    public function actionState()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $kfOut = new KfOut();
        $kfOut->scenario = KfOut::SCENARIO_STATE;
        $kfOut->load($kf->req, '');
        if (!$kfOut->validate()) {
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }

        $tcBank = new TCBank();
        $ret = $tcBank->confirmPay($kfOut->id, $kf->IdPartner);
        if ($ret && isset($ret['status'])) {
            $state = ['status' => (int)$ret['status'], 'message' => (string)$ret['message']];
        } else {
            $state = ['status' => 0, 'message' => 'Счет не найден'];
        }
        return $state;
    }

}