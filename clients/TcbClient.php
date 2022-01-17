<?php

namespace app\clients;

use app\clients\tcbClient\requests\DebitFinishRequest;
use app\clients\tcbClient\responses\DebitFinishResponse;
use app\clients\tcbClient\responses\ErrorResponse;
use app\models\payonline\Cards;
use clients\tcbClient\TcbInternalException;
use clients\tcbClient\TcbParsingException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Клиент к банку ТКБ.
 */
class TcbClient extends BaseObject
{
    private const TIMEOUT = 10;

    /**
     * @var string
     */
    protected $login;
    /**
     * @var string
     */
    protected $token;
    /**
     * @var string
     */
    protected $bankUrl;

    /**
     * @param string $login
     * @param string $token
     * @param string $bankUrl
     */
    public function __construct(string $login, string $token, string $bankUrl)
    {
        parent::__construct();

        $this->login = $login;
        $this->token = $token;
        $this->bankUrl = $bankUrl;
    }

    /**
     * @throws GuzzleException
     * @throws TcbParsingException
     * @throws TcbInternalException
     */
    protected function doRequest(string $endpoint, array $requestData)
    {
        $requestJson = Json::encode($requestData);

        $logData = [
            'Message' => '',
            'Login' => $this->login,
            'Url' => $this->bankUrl . $endpoint,
            'Request Data' => Cards::MaskCardLog($requestJson),
        ];

        \Yii::info(ArrayHelper::merge($logData, ['Message' => 'TCB request.']));

        $client = new Client();
        $headers = [
            'Content-type' => 'application/json',
            'TCB-Header-Login' => $this->login,
            'TCB-Header-Sign' => base64_encode(hash_hmac('SHA1', $requestJson, $this->token, true)),
            'TCB-Header-SerializerType' => 'Default',
        ];

        \Yii::info(ArrayHelper::merge($logData, ['Message' => 'TCB request start.']));
        $response = $client->request('POST', $this->bankUrl . $endpoint, [
            'timeout' => self::TIMEOUT,
            'connect_timeout' => self::TIMEOUT,
            'headers' => $headers,
            'verify' => false,
            'curl' => [
                CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
                CURLOPT_SSL_VERIFYPEER => false,
            ],
            'body' => $requestJson,
        ]);
        \Yii::info(ArrayHelper::merge($logData, ['Message' => 'TCB request end.']));

        $responseBody = $response->getBody()->getContents();

        if ($response->getStatusCode() === 500) {
            \Yii::error(ArrayHelper::merge($logData, [
                'Message' => 'TCB bank internal server error 500.',
                'Headers' => $headers,
                'Response' => $responseBody,
            ]));
            throw new TcbInternalException('Bank internal server error 500.');
        }

        try {
            $responseData = Json::decode($responseBody);
        } catch (InvalidArgumentException $e) {
            /** @todo Выяснить в каких случаях не удается декодировать тело ответа в JSON и правильно обработь. */
            \Yii::error(ArrayHelper::merge($logData, [
                'Message' => 'Unable to parse response.',
                'Headers' => $headers,
                'Response' => $responseBody,
            ]));
            throw new TcbParsingException('Unable to parse response.');
        }

        return $responseData;
    }

    /**
     * Осуществляет процедуру завершения операции по протоколу ECOM.
     *
     * @param DebitFinishRequest $request
     * @return DebitFinishResponse|ErrorResponse
     *
     * @throws GuzzleException
     * @throws TcbParsingException
     * @throws TcbInternalException
     */
    public function debitFinishEcom(DebitFinishRequest $request)
    {
        return $this->debitFinishInternal('/api/v1/card/unregistered/debit/wof/ecom/finish', $request);
    }

    /**
     * Осуществляет процедуру завершения операции по протоколу AFT.
     *
     * @param DebitFinishRequest $request
     * @return DebitFinishResponse|ErrorResponse
     *
     * @throws GuzzleException
     * @throws TcbParsingException
     * @throws TcbInternalException
     */
    public function debitFinishAft(DebitFinishRequest $request)
    {
        return $this->debitFinishInternal('/api/v1/card/unregistered/debit/wof/aft/finish', $request);
    }

    /**
     * @param string $endpoint
     * @param DebitFinishRequest $request
     * @return DebitFinishResponse|ErrorResponse
     *
     * @throws GuzzleException
     * @throws TcbParsingException
     * @throws TcbInternalException
     */
    private function debitFinishInternal(string $endpoint, DebitFinishRequest $request)
    {
        $responseData = $this->doRequest($endpoint, [
            'ExtId' => $request->extId,
            'Md' => $request->md,
            'PaRes' => $request->paRes,
        ]);

        if (isset($responseData['ErrorInfo']['ErrorCode'])) {
            $errorCode = (int)$responseData['ErrorInfo']['ErrorCode'];
            if ($errorCode !== 0) {
                return new ErrorResponse($responseData['ErrorInfo']['ErrorInfo'] ?? '', $errorCode);
            }
        }

        return new DebitFinishResponse($responseData['OrderId'], $responseData['ExtId']);
    }
}