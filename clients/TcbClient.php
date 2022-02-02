<?php

namespace app\clients;

use app\clients\tcbClient\requests\Debit3ds2FinishRequest;
use app\clients\tcbClient\requests\DebitFinishRequest;
use app\clients\tcbClient\responses\Debit3ds2FinishResponse;
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
 * TCB bank client.
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
     * DebitFinishECOM method.
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
     * DebitFinishAFT method.
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
                return new ErrorResponse($responseData['ErrorInfo']['ErrorMessage'] ?? '', $errorCode);
            }
        }

        return new DebitFinishResponse($responseData['OrderId'], $responseData['ExtId']);
    }

    /**
     * DebitUnregisteredCard3ds2WofFinish method.
     *
     * @param Debit3ds2FinishRequest $request
     * @return Debit3ds2FinishResponse|ErrorResponse
     * @throws GuzzleException
     * @throws TcbInternalException
     * @throws TcbParsingException
     */
    public function debit3ds2Finish(Debit3ds2FinishRequest $request)
    {
        $auth = $request->authenticationData;
        $authData = ['Status' => $auth->status];
        if ($auth->authenticationValue !== null) {
            $authData['AuthenticationValue'] = $auth->authenticationValue;
        }
        if ($auth->eci !== null) {
            $authData['Eci'] = $auth->eci;
        }
        if ($auth->dsTransId !== null) {
            $authData['DsTransID'] = $auth->dsTransId;
        }

        $responseData = $this->doRequest('/api/v1/card/unregistered/debit/3ds2/wof/finish', [
            'ExtId' => $request->extId,
            'Amount' => $request->amount,
            'ForceGate' => $request->forceGate,
            'Description' => $request->description,
            'CardInfo' => ['CardRefId' => $request->cardRefId],
            'AuthenticationData' => $authData,
        ]);

        if (isset($responseData['ErrorInfo']['Code'])) {
            $errorCode = (int)$responseData['ErrorInfo']['Code'];
            if ($errorCode !== 0) {
                return new ErrorResponse($responseData['ErrorInfo']['Message'] ?? '', $errorCode);
            }
        }

        return new Debit3ds2FinishResponse($responseData['OrderId'], $responseData['ExtId']);
    }
}