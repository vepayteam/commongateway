<?php

namespace app\services\yandex;

use app\models\PaySchetYandex;
use app\services\payment\models\PaySchet;
use app\services\yandex\models\DecryptedMessage;
use app\services\yandex\models\EncryptionKeyReader;
use yii\helpers\Json;

class PaymentHandlerService
{
    private const PROD_URL = 'https://pay.yandex.ru';
    private const DEV_URL = 'https://sandbox.pay.yandex.ru';

    /**
     * @param string $jsonDecryptedMessage
     * @param PaySchet $paySchet
     * @return DecryptedMessage
     */
    public function saveYandexTransaction(string $jsonDecryptedMessage, PaySchet $paySchet): DecryptedMessage
    {
        $decryptedMessage = new DecryptedMessage(Json::decode($jsonDecryptedMessage));

        $paySchetYandex = new PaySchetYandex();
        $paySchetYandex->paySchetId = $paySchet->ID;
        $paySchetYandex->messageId = $decryptedMessage->getMessageId();
        $paySchetYandex->decryptedMessage = $jsonDecryptedMessage;
        $paySchetYandex->save();

        return $decryptedMessage;
    }

    /**
     * @param PaySchet $paySchet
     * @return void
     * @throws \yii\base\InvalidConfigException
     * @throws exceptions\UpdateTransactionServiceException
     */
    public function updateYandexTransaction(PaySchet $paySchet)
    {
        /** @var UpdateTransactionService $updateTransactionService */
        $updateTransactionService = \Yii::$app->get(UpdateTransactionService::class);

        $encryptionKeyReader = $this->getEncryptionKeyReader($paySchet);
        $updateTransactionService->paymentUpdate($paySchet, $encryptionKeyReader);
    }

    /**
     * @param PaySchet $paySchet
     * @return EncryptionKeyReader
     */
    public function getEncryptionKeyReader(PaySchet $paySchet): EncryptionKeyReader
    {
        return new EncryptionKeyReader([
            'partner' => $paySchet->partner,
        ]);
    }

    /**
     * @param PaySchet $paySchet
     * @return bool
     */
    public function isYandexPayEnabled(PaySchet $paySchet): bool
    {
        return $paySchet->partner->isUseYandexPay;
    }

    /**
     * @param string $endpoint
     * @return string
     */
    public function getEndpoint(string $endpoint): string
    {
        return $this->getBaseUrl() . $endpoint;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        if (\Yii::$app->params['DEVMODE'] === 'Y' || \Yii::$app->params['TESTMODE'] === 'Y') {
            return self::DEV_URL;
        } else {
            return self::PROD_URL;
        }
    }
}
