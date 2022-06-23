<?php

namespace app\services;

use app\clients\YandexPayClient;
use app\clients\yandexPayClient\requests\PaymentUpdateRequest;
use app\helpers\Modifiers;
use app\models\PaySchetYandex;
use app\models\YandexPayRootKey;
use app\services\payment\models\PaySchet;
use app\services\yandexPay\models\DecryptedMessage;
use app\services\yandexPay\models\EncryptionKeyReader;
use app\services\yandexPay\models\PaymentToken;
use app\services\yandexPay\paymentToken\PaymentTokenDecrypt;
use app\services\yandexPay\paymentToken\PaymentTokenVerify;
use app\services\yandexPay\YandexPayException;
use GuzzleHttp\Exception\GuzzleException;
use yii\base\Component;
use yii\helpers\Json;

class YandexPayService extends Component
{
    private const PROD_URL = 'https://pay.yandex.ru';
    private const DEV_URL = 'https://sandbox.pay.yandex.ru';

    /**
     * Возвращает true если yandex pay включен в настройках контрагента
     *
     * @param PaySchet $paySchet
     * @return bool
     */
    public function isYandexPayEnabled(PaySchet $paySchet): bool
    {
        return $paySchet->partner->isUseYandexPay;
    }

    /**
     * Расшифровывает paymentToken и возвращает decryptedMessage
     *
     * @param PaymentToken $paymentToken
     * @param PaySchet $paySchet
     * @return DecryptedMessage
     * @throws YandexPayException
     */
    public function getDecryptedMessage(PaymentToken $paymentToken, PaySchet $paySchet): DecryptedMessage
    {
        $jsonDecryptedMessage = $this->decryptPaymentToken($paymentToken, $paySchet);
        return $this->saveYandexTransaction($jsonDecryptedMessage, $paySchet);
    }

    /**
     * Обновляет статус транзакции в системе yandex pay
     *
     * @param PaySchet $paySchet
     * @return void
     * @throws YandexPayException
     */
    public function paymentUpdate(PaySchet $paySchet)
    {
        $yandexPayTransaction = $paySchet->yandexPayTransaction;

        switch ($paySchet->Status) {
            case PaySchet::STATUS_DONE:
                $status = 'SUCCESS';
                break;
            case PaySchet::STATUS_ERROR:
                $status = 'FAIL';
                break;
            case PaySchet::STATUS_CANCEL:
                $status = 'REVERSE';
                break;
            case PaySchet::STATUS_REFUND_DONE:
                $status = 'REFUND';
                break;
            default:
                return;
        }

        $paymentUpdateRequest = new PaymentUpdateRequest(
            $yandexPayTransaction->messageId,
            date(\DateTimeInterface::RFC3339),
            $paySchet->getSummFull(),
            $paySchet->currency->Code,
            $status,
            $paySchet->RRN ?? '',
            $paySchet->ApprovalCode ?? '',
            $paySchet->Eci ?? '',
            $paySchet->RCCode ?? '',
            $paySchet->ErrorInfo ?? ''
        );

        $client = new YandexPayClient($this->getBaseUrl());

        try {
            $response = $client->paymentNotification($paymentUpdateRequest, $this->getEncryptionKeyReader($paySchet));
        } catch (GuzzleException $e) {
            throw new YandexPayException('Failed to update transaction, guzzle exception', 0, $e);
        }

        if ($response->getStatus() !== 'success') {
            \Yii::error('YandexPayService paymentUpdate failed to update transaction'
                . ' paySchet.ID=' . $paySchet->ID
            );

            throw new YandexPayException('Failed to update transaction paySchet.ID=' . $paySchet->ID);
        }
    }

    /**
     * Получает root ключи с серверов яндекса и обновляет их в бд
     *
     * @return void
     * @throws YandexPayException
     */
    public function updateKeys()
    {
        $client = new YandexPayClient($this->getBaseUrl());

        try {
            $keyListResponse = $client->keys();
        } catch (\Exception|GuzzleException $e) {
            throw new YandexPayException('Error loading keys', 0, $e);
        }

        if (count($keyListResponse->getKeys()) === 0) {
            \Yii::error('YandexPayService updateKeys bad root keys response: ');
            throw new YandexPayException('Bad root keys response');
        }

        $removeKeys = [];
        foreach ($keyListResponse->getKeys() as $key) {
            $removeKeys[] = $key->getKeyValue();

            $yandexPayRootKey = YandexPayRootKey::findOne(['keyValue' => $key->getKeyValue()]);
            if (!$yandexPayRootKey) {
                $yandexPayRootKey = new YandexPayRootKey([
                    'keyValue' => $key->getKeyValue(),
                    'keyExpiration' => $key->getKeyExpiration(),
                    'protocolVersion' => $key->getProtocolVersion(),
                ]);
                $yandexPayRootKey->save();
            }
        }

        /**
         * Ключи, которых нет в списке удаляем
         */
        YandexPayRootKey::deleteAll([
            'not in',
            'keyValue',
            $removeKeys,
        ]);
    }

    /**
     * Сохраняет транзакцию yandex в бд и возвращает DecryptedMessage
     *
     * @param string $jsonDecryptedMessage
     * @param PaySchet $paySchet
     * @return DecryptedMessage
     */
    private function saveYandexTransaction(string $jsonDecryptedMessage, PaySchet $paySchet): DecryptedMessage
    {
        $decryptedMessage = new DecryptedMessage(Json::decode($jsonDecryptedMessage));

        $paySchetYandex = new PaySchetYandex();
        $paySchetYandex->paySchetId = $paySchet->ID;
        $paySchetYandex->messageId = $decryptedMessage->getMessageId();
        $paySchetYandex->decryptedMessage = Modifiers::searchAndReplacePan($jsonDecryptedMessage);
        $paySchetYandex->save();

        return $decryptedMessage;
    }

    /**
     * Верфиицирует и расшифровывает payment token
     *
     * @param PaymentToken $paymentToken
     * @param PaySchet $paySchet
     * @return string
     * @throws YandexPayException
     */
    private function decryptPaymentToken(PaymentToken $paymentToken, PaySchet $paySchet): string
    {
        $rootKeys = $this->getKeys();

        $paymentTokenVerify = new PaymentTokenVerify();
        $paymentTokenVerify->validate($paymentToken, $rootKeys);

        $encryptionKeyReader = $this->getEncryptionKeyReader($paySchet);

        $paymentTokenDecrypt = new PaymentTokenDecrypt();
        return $paymentTokenDecrypt->decrypt($paymentToken, $encryptionKeyReader);
    }

    /**
     * @return YandexPayRootKey[]
     * @throws YandexPayException
     */
    private function getKeys(): array
    {
        $yandexPayRootKeys = YandexPayRootKey::find()->all();
        if (count($yandexPayRootKeys) > 0) {
            return $yandexPayRootKeys;
        }

        \Yii::info('YandexPayService getKeys no keys found in db');
        $this->updateKeys();

        return YandexPayRootKey::find()->all();
    }

    /**
     * @param PaySchet $paySchet
     * @return EncryptionKeyReader
     */
    private function getEncryptionKeyReader(PaySchet $paySchet): EncryptionKeyReader
    {
        return new EncryptionKeyReader([
            'partner' => $paySchet->partner,
        ]);
    }

    /**
     * @return string
     */
    private function getBaseUrl(): string
    {
        if (\Yii::$app->params['DEVMODE'] === 'Y' || \Yii::$app->params['TESTMODE'] === 'Y') {
            return self::DEV_URL;
        } else {
            return self::PROD_URL;
        }
    }
}