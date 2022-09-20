<?php

namespace app\controllers;

use app\models\api\CorsTrait;
use app\models\PaySchetAcsRedirect;
use app\services\callbacks\forms\ImpayaCallbackForm;
use app\services\callbacks\forms\MonetixCallbackForm;
use app\services\callbacks\forms\MonetixCallbackPingForm;
use app\services\callbacks\forms\PaylerCallbackForm;
use app\services\callbacks\ImpayaCallbackService;
use app\services\callbacks\MonetixCallbackService;
use app\services\payment\models\PaySchet;
use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class CallbackController extends Controller
{
    use CorsTrait;

    private const MONETIX_3DS_STATUS = 'awaiting 3ds result';
    private const MONETIX_REDIRECT_STATUS = 'awaiting redirect result';

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

    /**
     * Задача этого callback`а записать recurrent_template_id в карту.
     * Тк payler не хочет возвращать recurrent_template_id при опросе статуса, а возвращает исключительно в виде callback
     *
     * TODO вынести функционал в отдельный сервис?
     *
     * @return Response
     */
    public function actionPayler(): Response
    {
        $form = new PaylerCallbackForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            Yii::error([
                'Message' => 'CallbackController payler invalid post data',
                'Errors' => Json::encode($form->getErrors()),
                'Post Data' => Json::encode(Yii::$app->request->post()),
            ]);

            return $this->asJson([
                'status' => false,
            ]);
        }

        if ($form->getRecurrentTemplateId() === null) {
            Yii::info([
                'Message' => 'CallbackController payler recurrent template id is null',
                'Order Id' => $form->order_id,
            ]);

            return $this->asJson([
                'status' => true,
            ]);
        }

        $paySchet = PaySchet::findOne(['ID' => $form->getOrderId()]);

        $card = $paySchet->cards;
        $card->ExtCardIDP = $form->getRecurrentTemplateId();
        $card->save(false);

        return $this->asJson([
            'status' => true,
        ]);
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

        $paySchet = $monetixCallbackForm->getPaySchet();
        $acsRedirect = $paySchet->acsRedirect;
        if ($acsRedirect !== null) {
            if ($data['operation']['status'] === self::MONETIX_3DS_STATUS) {
                $acsRedirect->status = PaySchetAcsRedirect::STATUS_OK;
                $acsRedirect->url = $data['acs']['acs_url'];
                $acsRedirect->method = PaySchetAcsRedirect::METHOD_POST;
                $acsRedirect->postParameters = [
                    'MD' => $data['acs']['md'],
                    'PaReq' => $data['acs']['pa_req'],
                    'TermUrl' => $data['acs']['term_url'],
                ];
                $acsRedirect->save(false);
            } elseif ($data['operation']['status'] === self::MONETIX_REDIRECT_STATUS) {
                $acsRedirect->status = PaySchetAcsRedirect::STATUS_OK;
                $acsRedirect->url = $data['redirect_data']['url'];
                $acsRedirect->method = $data['redirect_data']['method'];
                if (!empty($data['redirect_data']['body'])) {
                    $acsRedirect->postParameters = $data['redirect_data']['body'];
                } else {
                    $acsRedirect->postParameters = null;
                }
                $acsRedirect->save(false);
            }
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