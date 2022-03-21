<?php

namespace app\controllers;

use app\models\api\CorsTrait;
use app\services\callbacks\forms\ImpayaCallbackForm;
use app\services\callbacks\ImpayaCallbackService;
use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
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

}