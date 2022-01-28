<?php

namespace app\modules\kfapi\controllers;

use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfRequest;
use app\models\kfapi\KfBenific;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\payment_strategies\RegistrationBenificStrategy;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;


class BenificController extends Controller
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
            'reg' => ['POST'],
        ];
    }

    /**
     * Регистрация бенефициаров
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionReg()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $registrationBenificForm = new RegistrationBenificForm();
        $registrationBenificForm->load($kf->req, '');
        $registrationBenificForm->partner = $kf->partner;
        if(!$registrationBenificForm->validate()) {
            return ['status' => 0, 'message' => $registrationBenificForm->GetError()];
        }

        $registrationBenificStrategy = new RegistrationBenificStrategy($registrationBenificForm);
        try {
            $registrationBenificResponse = $registrationBenificStrategy->exec();
        } catch (\Exception $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        if($registrationBenificResponse->status == BaseResponse::STATUS_DONE) {
            return ['status' => 1, 'message' => $registrationBenificResponse->response];
        } else {
            return ['status' => 0, 'message' => $registrationBenificResponse->message];
        }
    }

    /**
     * Регистрация бенефициаров файлом
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionRegfile()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        if (empty($kf->partner->SchetTcbNominal)) {
            return ['status' => 0, 'message' => 'Номинальный счет не найден'];
        }

        $KfBenific = new KfBenific();
        $data = $KfBenific->EncodeFile($kf->GetReq('data'));
        if (!$data) {
            return ['status' => 0, 'message' => 'Неверный формат файла'];
        }

        $TcbGate = new TcbGate($kf->IdPartner, TCBank::$AFTGATE);
        $tcBank = new TCBank($TcbGate);
        $ret = $tcBank->RegisterBenificiar([
            'req' => $data
        ]);

        if (isset($ret['status']) && $ret['status'] == 1) {
            $KfBenific = new KfBenific();
            $KfBenific->setAttributes(['result' => $ret['soap']]);
            $ret = $KfBenific->ParseResult();
            return ['status' => $ret['error'] == 0 ? 1 : 0, 'message' => $ret['message']];
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];

    }
}
