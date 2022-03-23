<?php

namespace app\clients;

use app\clients\tcbClient\requests\Debit3ds2FinishRequest;
use app\clients\tcbClient\requests\DebitFinishRequest;
use app\clients\tcbClient\requests\GetOrderStateRequest;
use app\clients\tcbClient\responses\Debit3ds2FinishResponse;
use app\clients\tcbClient\responses\DebitFinishResponse;
use app\clients\tcbClient\responses\ErrorResponse;
use app\clients\tcbClient\responses\GetOrderStateResponse;
use app\clients\tcbClient\responses\objects\OrderAdditionalInfo;
use app\clients\tcbClient\responses\objects\OrderInfo;
use app\clients\tcbClient\TcbOrderNotExistException;
use app\models\payonline\Cards;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * TCB bank client.
 */
class TcbClient extends BaseObject
{
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
     * @var int
     */
    protected $connectionTimeout;

    /**
     * @param string $login
     * @param string $token
     * @param string $bankUrl
     */
    public function __construct(
        string $login,
        string $token,
        string $bankUrl,
        int $connectionTimeout
    )
    {
        parent::__construct();

        $this->login = $login;
        $this->token = $token;
        $this->bankUrl = $bankUrl;
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * @throws GuzzleException
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

        $headers = [
            'Content-type' => 'application/json',
            'TCB-Header-Login' => $this->login,
            'TCB-Header-Sign' => base64_encode(hash_hmac('SHA1', $requestJson, $this->token, true)),
            'TCB-Header-SerializerType' => 'Default',
        ];

        \Yii::info(ArrayHelper::merge($logData, ['Message' => 'TCB request start.']));

        try {
            $response = (new Client())->request('POST', $this->bankUrl . $endpoint, [
                'timeout' => $this->connectionTimeout,
                'connect_timeout' => $this->connectionTimeout,
                'headers' => $headers,
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
                    CURLOPT_SSL_VERIFYPEER => false,
                ],
                'body' => $requestJson,
            ]);
        } catch (BadResponseException $e) {
            \Yii::error(ArrayHelper::merge($logData, [
                'Message' => "TCB bad response error. Status code: {$e->getResponse()->getStatusCode()}.",
                'Headers' => $headers,
                'Response' => Cards::MaskCardLog((string)$e->getResponse()->getBody()),
            ]));
            throw $e;
        }

        $responseBody = (string)$response->getBody();

        \Yii::info(ArrayHelper::merge($logData, [
            'Message' => 'TCB request end.',
            'Response' => Cards::MaskCardLog($responseBody),
        ]));

        return $this->tryJsonDecode($responseBody);
    }

    private function tryJsonDecode(string $json)
    {
        try {
            return Json::decode($json);
        } catch (InvalidArgumentException $e) {
            \Yii::error('Unable to parse JSON: ' . Cards::MaskCardLog($json));
        }
        return null;
    }

    /**
     * DebitFinishECOM method.
     *
     * @param DebitFinishRequest $request
     * @return DebitFinishResponse|ErrorResponse
     *
     * @throws GuzzleException
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

    /**
     * GetOrderState method.
     *
     * Throws special exception {@see TcbOrderNotExistException}.
     *
     * @param GetOrderStateRequest $request
     * @return GetOrderStateResponse|ErrorResponse
     * @throws GuzzleException
     * @throws TcbOrderNotExistException
     */
    public function getOrderState(GetOrderStateRequest $request)
    {
        try {

            $responseData = $this->doRequest('/api/v1/order/state', [
                'ExtID' => $request->extId,
            ]);

        } catch (ServerException $e) {
            /** @todo Remove, hack VPBC-1298. */
            $errorData = $this->tryJsonDecode((string)$e->getResponse()->getBody());
            if (is_array($errorData) && $errorData['Code'] === 'OrderNotExist') {
                \Yii::$app->errorHandler->logException($e);
                throw new TcbOrderNotExistException();
            }
            throw $e;
        }

        $errorCode = (int)$responseData['ErrorInfo']['ErrorCode'];
        if ($errorCode !== 0) {
            return new ErrorResponse($responseData['ErrorInfo']['ErrorMessage'] ?? '', $errorCode);
        }

        $infoData = $responseData['OrderInfo'];
        $info = new OrderInfo(
            $infoData['ExtId'],
            $infoData['OrderId'],
            $infoData['State'],
            $infoData['StateDescription'],
            $infoData['Type'],
            $infoData['Amount'],
            new Carbon($infoData['DateTime']),
            new Carbon($infoData['StateUpdateDateTime'])
        );

        if (isset($responseData['OrderAdditionalInfo'])) {
            $additionalInfoData = $responseData['OrderAdditionalInfo'];
            $additionalInfo = new OrderAdditionalInfo(
                $additionalInfoData['CardExpYear'] ?? null,
                $additionalInfoData['CardExpMonth'] ?? null,
                $additionalInfoData['CardIssuingBank'] ?? null,
                $additionalInfoData['CardBrand'] ?? null,
                $additionalInfoData['CardType'] ?? null,
                $additionalInfoData['CardLevel'] ?? null,
                $additionalInfoData['LastStateDate'] ?? null,
                $additionalInfoData['CardNumber'] ?? null,
                $additionalInfoData['CardHolder'] ?? null,
                $additionalInfoData['CardRefID'] ?? null,
                $additionalInfoData['ActionCodeDescription'] ?? null,
                $additionalInfoData['ECI'] ?? null,
                $additionalInfoData['CardNumberHash'] ?? null,
                $additionalInfoData['RC'] ?? null,
                $additionalInfoData['Fee'] ?? null,
                $additionalInfoData['RRN'] ?? null
            );
        } else {
            $additionalInfo = null;
        }

        return new GetOrderStateResponse($info, $additionalInfo);
    }
}