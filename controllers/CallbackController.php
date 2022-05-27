<?php

namespace app\controllers;

use app\models\api\CorsTrait;
use app\models\PaySchetAcsRedirect;
use app\services\callbacks\forms\ImpayaCallbackForm;
use app\services\callbacks\forms\MonetixCallbackForm;
use app\services\callbacks\forms\MonetixCallbackPingForm;
use app\services\callbacks\ImpayaCallbackService;
use app\services\callbacks\MonetixCallbackService;
use app\services\payment\models\PaySchet;
use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class CallbackController extends Controller
{
    use CorsTrait;

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
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

    public function actionImpaya(): Response
    {
        $impayaCallbackForm = new ImpayaCallbackForm();
        if(!$impayaCallbackForm->load(Yii::$app->request->post(), '') || !$impayaCallbackForm->validate()) {
            $log = sprintf(
                'Callback Impaya error: %s, data=%s',
                $impayaCallbackForm->GetError(),
                Json::encode(Yii::$app->request->post())
            );
            Yii::info($log);
            return $this->asJson(['status' => 0]);
        } else {
            $log = sprintf(
                'Callback Impaya id=%d, status=%d, data=%s',
                $impayaCallbackForm->mc_transaction_id,
                $impayaCallbackForm->status_id,
                Json::encode($impayaCallbackForm->getAttributes())
            );
            Yii::info($log);
            $impayaCallbackService = new ImpayaCallbackService();
            $impayaCallbackService->execCallback($impayaCallbackForm);
            return $this->asJson(['status' => 1]);
        }
    }

    public function actionMonetix()
    {

        $log = sprintf(
            'Callback Monetix remote=%s data=%s',
            Yii::$app->request->remoteIP,
            Json::encode(Yii::$app->request->post())
        );
        Yii::info($log);
        $data = Yii::$app->request->post();

        $monetixCallbackForm = new MonetixCallbackForm();
        $monetixCallbackForm->paySchetId = $data['customer']['id'] ?? null;
        $monetixCallbackForm->status = $data['operation']['status'] ?? null;
        $monetixCallbackForm->message = $data['operation']['description'] ?? null;
        $monetixCallbackForm->transId = $data['operation']['request_id'] ?? null;
        $monetixCallbackForm->data = $data;

        if(!$monetixCallbackForm->validate()) {
            return $this->asJson(['status' => 0]);
        }

        $monetixCallbackService = new MonetixCallbackService();
        $monetixCallbackService->execCallback($monetixCallbackForm);


        $paySchet = PaySchet::findOne((int)$data['customer']['id']);
        if (
            $data['operation']['status'] === 'awaiting 3ds result'
            && $paySchet->acsRedirect !== null
        ) {
            $paySchet->acsRedirect->status = PaySchetAcsRedirect::STATUS_OK;
            $paySchet->acsRedirect->url = $data['acs']['acs_url'];
            $paySchet->acsRedirect->method = PaySchetAcsRedirect::METHOD_POST;
            $paySchet->acsRedirect->postParameters = [
                'MD' => $data['acs']['md'],
                'PaReq' => $data['acs']['pa_req'],
                'TermUrl' => $data['acs']['term_url'],
            ];
            $paySchet->acsRedirect->save(false);
        }

        return $this->asJson(['status' => 1]);
    }

    public function actionMonetixPing()
    {
        $monetixCallbackPingForm = new MonetixCallbackPingForm();
        $monetixCallbackPingForm->load(Yii::$app->request->post(), '');

        if(!$monetixCallbackPingForm->validate()) {
            throw new BadRequestHttpException();
        }

        $monetixCallbackService = new MonetixCallbackService();
        $response = $monetixCallbackService->getCallbackData($monetixCallbackPingForm->getPaySchet());
        return $this->asJson($response);
    }

}