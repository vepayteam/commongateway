<?php

namespace app\controllers;

use app\services\LanguageService;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\DuplicateCreatePayException;
use app\services\payment\exceptions\FailPaymentException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\reRequestingStatusException;
use app\services\payment\exceptions\reRequestingStatusOkException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreatePayStrategy;
use app\services\yandexPay\forms\YandexPayForm;
use app\services\yandexPay\models\PaymentToken;
use app\services\YandexPayService;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\rest\Controller;

class YandexPayController extends Controller
{
    /**
     * @inheritdoc
     */
    public function verbs(): array
    {
        return [
            'create-pay' => ['POST'],
        ];
    }

    public function actionCreatePay($id)
    {
        $paySchet = PaySchet::findOne(['ID' => $id]);
        if (!$paySchet) {
            return [
                'status' => 0,
                'message' => 'Платеж не найден',
            ];
        }

        /** @var YandexPayService $yandexPayService */
        $yandexPayService = \Yii::$app->get(YandexPayService::class);
        if (!$yandexPayService->isYandexPayEnabled($paySchet)) {
            return [
                'status' => 0,
                'message' => 'Yandex Pay не подключен',
            ];
        }

        $yandexPayForm = new YandexPayForm();
        if (!$yandexPayForm->load(\Yii::$app->request->post(), '') || !$yandexPayForm->validate()) {
            return [
                'status' => 0,
                'message' => $yandexPayForm->firstErrors[0],
            ];
        }

        $paymentToken = new PaymentToken($yandexPayForm->paymentToken);

        try {
            $decryptedMessage = $yandexPayService->getDecryptedMessage($paymentToken, $paySchet);
        } catch (\Exception $e) {
            \Yii::error([
                'YandexPayController actionCreatePay payment token decrypt exception',
                $yandexPayForm->paymentToken,
                $e
            ]);

            return [
                'status' => 0,
                'message' => 'Ошибка запроса',
            ];
        }

        $createPayForm = new CreatePayForm([
            'CardNumber' => $decryptedMessage->getPaymentMethodDetails()->getPan(),
            'CardExp' => $decryptedMessage->getPaymentMethodDetails()->getFullExpiration(),
            'IdPay' => $id,
        ]);
        $createPayForm->afterValidate();

        /** @var LanguageService $languageService */
        $languageService = \Yii::$app->get(LanguageService::class);
        $languageService->setAppLanguage($id);

        \Yii::warning("YandexPayController actionCreatePay id=" . $id);
        \Yii::$app->session->set('IdPay', $id);

        $createPayStrategy = new CreatePayStrategy($createPayForm);

        try {
            $paySchet = $createPayStrategy->exec();
        } catch (DuplicateCreatePayException $e) {
            // releaseLock сюда не надо, эксепшен вызывается при попытке провести платеж, который уже проведен
            \Yii::$app->errorHandler->logException($e);

            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (reRequestingStatusOkException|FailPaymentException $e) {
            \Yii::$app->errorHandler->logException($e);
            $createPayStrategy->releaseLock();

            return [
                'status' => 2,
                'message' => $e->getMessage(),
                'url' => Url::to(['orderok', 'id' => $id]),
            ];
        } catch (Check3DSv2Exception $e) {
            \Yii::$app->errorHandler->logException($e);
            $createPayStrategy->releaseLock();

            return [
                'status' => 0,
                'message' => \Yii::t('app.payment-errors', 'Карта не поддерживается, обратитесь в банк')
            ];
        } catch (CreatePayException|GateException|reRequestingStatusException|BankAdapterResponseException|\Exception $e) {
            \Yii::$app->errorHandler->logException($e);
            $createPayStrategy->releaseLock();

            return ['status' => 0, 'message' => $e->getMessage()];
        }

        $createPayResponse = $createPayStrategy->getCreatePayResponse();
        $createPayResponse->termurl = $createPayResponse->GetRetUrl($paySchet->ID);
        switch ($createPayResponse->status) {
            case BaseResponse::STATUS_DONE:
                // отправить запрос адреса формы 3ds
                \Yii::warning('YandexPayController createPayResponse data: ' . Json::encode($createPayResponse->getAttributes()));
                return $createPayResponse->getAttributes();
            case BaseResponse::STATUS_ERROR:
                // отменить счет
                return $this->redirect(Url::to('/pay/orderok?id=' . $id));
            case BaseResponse::STATUS_CREATED:
                $createPayResponse->termurl = $createPayResponse->getStep2Url($paySchet->ID);
                return $createPayStrategy->getCreatePayResponse()->getAttributes();
            default:
                return $createPayStrategy->getCreatePayResponse()->getAttributes();
        }
    }
}
