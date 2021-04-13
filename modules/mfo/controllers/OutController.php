<?php

namespace app\modules\mfo\controllers;

use app\models\antifraud\AntiFraud;
use app\models\antifraud\AntiFraudRefund;
use app\models\api\CorsTrait;
use app\models\bank\BankMerchant;
use app\models\bank\Banks;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\CardToken;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfOut;
use app\models\mfo\MfoReq;
use app\models\mfo\MfoTestError;
use app\models\payonline\Cards;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayaccForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\mfo\MfoOutCardStrategy;
use app\services\payment\payment_strategies\mfo\MfoOutPayaccStrategy;
use Vepay\Gateway\Client\Validator\ValidationException;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class OutController extends Controller
{
    use CorsTrait;

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

    protected function verbs()
    {
        return [
            'paycard' => ['POST'],
            'payacc' => ['POST'],
            'payul' => ['POST'],
            'state' => ['POST'],
        ];
    }

    /**
     * Выплата займа на карту
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionPaycard()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());
        Yii::warning("mfo/out/paycard Authorization mfo=$mfo->mfo", 'mfo_out_paycard');

        $outCardPayForm = new OutCardPayForm();
        $outCardPayForm->partner = $mfo->getPartner();
        $outCardPayForm->load($mfo->Req(), '');
        if (!$outCardPayForm->validate()) {
            Yii::warning("out/paycard: " . $outCardPayForm->GetError(), 'mfo');
            return ['status' => 0, 'message' => $outCardPayForm->GetError()];
        }
        // рубли в коп
        $outCardPayForm->amount *= 100;

        $mfoOutCardStrategy = new MfoOutCardStrategy($outCardPayForm);

        try {
            $paySchet = $mfoOutCardStrategy->exec();
            return ['status' => 1, 'id' => $paySchet->ID, 'message' => ''];
        } catch (CardTokenException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (CreatePayException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (ValidationException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }
    }

    /**
     * Выплата займа на счет физлица
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionPayacc()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $outPayaccForm = new OutPayaccForm();
        $outPayaccForm->scenario = OutPayaccForm::SCENARIO_FL;
        $outPayaccForm->load($mfo->Req(), '');

        if (!$outPayaccForm->validate()) {
            Yii::warning("out/payul: " . $outPayaccForm->GetError(), 'mfo');
            return ['status' => 0, 'message' => $outPayaccForm->GetError()];
        }
        $outPayaccForm->partner = $mfo->getPartner();

        $mfoOutPayaccStrategy = new MfoOutPayaccStrategy($outPayaccForm);
        try {
            $paySchet = $mfoOutPayaccStrategy->exec();
        } catch (CreatePayException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        return ['status' => 1, 'id' => $paySchet->ID, 'message' => ''];
    }

    /**
     * Выплата займа на счет юрлица
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionPayul()
    {
        // TODO: DRY
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $outPayaccForm = new OutPayaccForm();
        $outPayaccForm->scenario = OutPayaccForm::SCENARIO_UL;
        $outPayaccForm->load($mfo->Req(), '');

        if (!$outPayaccForm->validate()) {
            Yii::warning("out/payacc: " . $outPayaccForm->GetError(), 'mfo');
            return ['status' => 0, 'message' => $outPayaccForm->GetError()];
        }
        $outPayaccForm->partner = $mfo->getPartner();

        $mfoOutPayaccStrategy = new MfoOutPayaccStrategy($outPayaccForm);
        try {
            $paySchet = $mfoOutPayaccStrategy->exec();
            return [
                'status' => $mfoOutPayaccStrategy->transferToAccountResponse->status,
                'id' => $paySchet->ID,
                'message' => $mfoOutPayaccStrategy->transferToAccountResponse->message,
            ];
        } catch (CreatePayException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $IdPay = $mfo->GetReq('id');
        $paySchet = PaySchet::findOne(['ID' => $IdPay]);
        if(!$paySchet) {
            return ['status' => 0, 'message' => 'Счет не найден'];
        } else {
            $status = (int)$paySchet->Status;
            $message = $paySchet->ErrorInfo;

            // Если платеж ожидает проверку статуса в очереди, пользователю возвращаем статус 0, чтобы соотв документации
            if($status == PaySchet::STATUS_WAITING_CHECK_STATUS) {
                $status = PaySchet::STATUS_WAITING;
                $message = 'Ожидается обновление статуса';
            }

            return [
                'status' => $status,
                'message' => $message,
                'rc' => $paySchet->RCCode,
            ];
        }
    }
}
