<?php

namespace app\clients;

use app\clients\yandexPayClient\requests\PaymentUpdateRequest;
use app\clients\yandexPayClient\responses\PaymentUpdateResponse;
use app\clients\yandexPayClient\responses\RootKeyListResponse;
use app\clients\yandexPayClient\responses\objects\RootKey;
use app\services\yandexPay\models\EncryptionKeyReader;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class YandexPayClient extends BaseObject
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        parent::__construct();

        $this->baseUrl = $baseUrl;
    }

    /**
     * Обновляет транзацкию в системе yandex pay
     *
     * @param PaymentUpdateRequest $paymentUpdateRequest
     * @param EncryptionKeyReader $encryptionKeyReader
     * @return PaymentUpdateResponse
     * @throws GuzzleException
     */
    public function paymentNotification(PaymentUpdateRequest $paymentUpdateRequest, EncryptionKeyReader $encryptionKeyReader): PaymentUpdateResponse
    {
        $endpoint = '/api/psp/v1/payment_notification';
        $method = 'POST';

        $requestData = [
            'messageId' => $paymentUpdateRequest->getMessageId(),
            'eventTime' => $paymentUpdateRequest->getEventTime(),
            'amount' => $paymentUpdateRequest->getAmount(),
            'currency' => $paymentUpdateRequest->getCurrency(),
            'status' => $paymentUpdateRequest->getStatus(),
            'rrn' => $paymentUpdateRequest->getRrn(),
            'approvalCode' => $paymentUpdateRequest->getApprovalCode(),
            'eci' => $paymentUpdateRequest->getEci(),
            'reasonCode' => $paymentUpdateRequest->getReasonCode(),
            'reason' => $paymentUpdateRequest->getReason(),
        ];

        $bearerToken = $this->getBearerToken($endpoint, $method, $requestData, $encryptionKeyReader);
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ];

        $response = $this->doRequest($endpoint, $method, $requestData, $headers);

        return new PaymentUpdateResponse($response['status']);
    }

    /**
     * Возвращает root ключи для верификации payment token
     *
     * @return RootKeyListResponse
     * @throws GuzzleException
     */
    public function keys(): RootKeyListResponse
    {
        $response = $this->doRequest('/api/v1/keys/keys.json', 'GET');

        $keys = [];
        $keysResponse = $response['keys'];
        foreach ($keysResponse as $keyResponse) {
            $keys[] = new RootKey($keyResponse['protocolVersion'], $keyResponse['keyValue'], $keyResponse['keyExpiration']);
        }

        return new RootKeyListResponse($keys);
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array|null $requestData
     * @param array|null $headers
     * @return mixed|null
     * @throws GuzzleException
     */
    private function doRequest(string $endpoint, string $method, array $requestData = null, array $headers = null)
    {
        $logData = [
            'Message' => '',
            'Url' => $this->baseUrl . $endpoint,
            'Request Data' => Json::encode($requestData),
        ];

        \Yii::info(ArrayHelper::merge($logData, [
            'Message' => 'YandexPay request start'
        ]));

        try {
            $response = (new Client())->request($method, $this->baseUrl . $endpoint, [
                'body' => Json::encode($requestData),
                'headers' => $headers,
            ]);
        } catch (BadResponseException $e) {
            \Yii::error(ArrayHelper::merge($logData, [
                'Message' => "YandexPay bad response. Status code: {$e->getResponse()->getStatusCode()}.",
                'Headers' => $headers,
                'Response Data' => (string)$e->getResponse()->getBody(),
            ]));
            throw $e;
        }

        $responseBody = (string)$response->getBody();

        \Yii::info(ArrayHelper::merge($logData, [
            'Message' => 'YandexPay request end',
            'Response Data' => $responseBody,
        ]));

        return Json::decode($responseBody);
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array $requestData
     * @param EncryptionKeyReader $encryptionKeyReader
     * @return string
     */
    private function getBearerToken(string $endpoint, string $method, array $requestData, EncryptionKeyReader $encryptionKeyReader): string
    {
        $token = [
            $method,
            $endpoint,
            '',
            Json::encode($requestData),
        ];
        $token = join('&', $token);

        $privateKey = openssl_pkey_get_private($encryptionKeyReader->readPrivateAuthKey());

        $header = [
            'alg' => 'ES256',
            'iat' => time(),
            'kid' => '1-vepay',
        ];

        $encodedHeader = JWT::urlsafeB64Encode(JWT::jsonEncode($header));
        $encodedToken = JWT::urlsafeB64Encode($token);

        $message = $encodedHeader . '.' . $encodedToken;
        $sign = JWT::sign($message, $privateKey, 'ES256');

        return $encodedHeader . '..' . JWT::urlsafeB64Encode($sign);
    }
}
