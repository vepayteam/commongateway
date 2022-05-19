<?php

namespace app\services\yandex;

use app\Api\Client\Client;
use app\services\payment\models\PaySchet;
use app\services\yandex\exceptions\UpdateTransactionServiceException;
use app\services\yandex\models\EncryptionKeyReader;
use Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;
use yii\helpers\Json;

class UpdateTransactionService
{
    private const PAYMENT_NOTIFICATION_ENDPOINT = '/api/psp/v1/payment_notification';

    /**
     * @param PaySchet $paySchet
     * @param EncryptionKeyReader $encryptionKeyReader
     * @return void
     * @throws UpdateTransactionServiceException
     * @throws \yii\base\InvalidConfigException
     */
    public function paymentUpdate(PaySchet $paySchet, EncryptionKeyReader $encryptionKeyReader)
    {
        $yandexPayTransaction = $paySchet->yandexPayTransaction;

        $requestData = [
            'messageId' => $yandexPayTransaction->messageId,
            'eventTime' => date(\DateTimeInterface::RFC3339),
            'amount' => $paySchet->getSummFull(),
            'currency' => $paySchet->currency->Code,
        ];

        switch ($paySchet->Status) {
            case PaySchet::STATUS_DONE:
                $requestData['status'] = 'SUCCESS';
                $requestData['rrn'] = $paySchet->RRN ?? '';
                $requestData['approvalCode'] = $paySchet->ApprovalCode ?? '';
                $requestData['eci'] = $paySchet->Eci ?? '';
                break;
            case PaySchet::STATUS_ERROR:
                $requestData['status'] = 'FAIL';
                $requestData['reasonCode'] = $paySchet->RCCode ?? '';
                $requestData['reason'] = $paySchet->ErrorInfo ?? '';
                break;
            case PaySchet::STATUS_CANCEL:
                $requestData['status'] = 'REVERSE';
                break;
            case PaySchet::STATUS_REFUND_DONE:
                $requestData['status'] = 'REFUND';
                break;
            default:
                return;
        }

        $requestJson = Json::encode($requestData);
        $requestMethod = 'POST';

        $bearerToken = $this->getBearerToken($requestJson,
            $requestMethod,
            self::PAYMENT_NOTIFICATION_ENDPOINT,
            $encryptionKeyReader
        );

        /** @var PaymentHandlerService $paymentHandlerService */
        $paymentHandlerService = \Yii::$app->get(PaymentHandlerService::class);

        $client = new Client();

        try {
            $response = $client->request($requestMethod,
                $paymentHandlerService->getEndpoint(self::PAYMENT_NOTIFICATION_ENDPOINT),
                $requestData,
                [
                    'Authorization' => 'Bearer ' . $bearerToken,
                    'Content-Type' => 'application/json',
                ]
            );
        } catch (GuzzleException $e) {
            throw new UpdateTransactionServiceException('Failed to update transaction, guzzle exception', 0, $e);
        }

        $jsonResponse = $response->json();
        if ($jsonResponse['status'] !== 'success') {
            \Yii::error('UpdateTransactionService paymentUpdate failed to update transaction'
                . ' paySchet.ID=' . $paySchet->ID
                . ' response=' . Json::encode($jsonResponse)
            );

            throw new UpdateTransactionServiceException('Failed to update transaction paySchet.ID=' . $paySchet->ID);
        }
    }

    /**
     * @param string $requestJson
     * @param string $requestMethod
     * @param string $endpoint
     * @param EncryptionKeyReader $encryptionKeyReader
     * @return string
     */
    private function getBearerToken(string $requestJson, string $requestMethod, string $endpoint, EncryptionKeyReader $encryptionKeyReader): string
    {
        $token = [
            $requestMethod,
            $endpoint,
            '',
            $requestJson,
        ];
        $token = join('&', $token);

        $privateKey = openssl_pkey_get_private($encryptionKeyReader->readPrivateAuthKey());

        $header = [
            'alg' => 'ES256',
            'iat' => time(),
            'kid' => '1-vepay',
        ];

        $encodedHeader = JWT::urlsafeB64Encode(JWT::jsonEncode($header));

        $message = $encodedHeader . '.' . JWT::urlsafeB64Encode($token);
        $sign = JWT::sign($message, $privateKey, 'ES256');

        return $encodedHeader . '..' . JWT::urlsafeB64Encode($sign);
    }
}
