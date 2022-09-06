<?php

namespace app\clients;

use app\clients\cauriClient\objects\AcsDataObject;
use app\clients\cauriClient\objects\AcsParametersObject;
use app\clients\cauriClient\objects\CardDataObject;
use app\clients\cauriClient\objects\RecurringDataObject;
use app\clients\cauriClient\requests\CardAuthenticateRequest;
use app\clients\cauriClient\requests\CardGetTokenRequest;
use app\clients\cauriClient\requests\CardProcessRecurringRequest;
use app\clients\cauriClient\requests\CardProcessRequest;
use app\clients\cauriClient\requests\TransactionRefundRequest;
use app\clients\cauriClient\requests\TransactionReverseRequest;
use app\clients\cauriClient\requests\TransactionStatusRequest;
use app\clients\cauriClient\requests\UserResolveRequest;
use app\clients\cauriClient\responses\CardAuthenticateResponse;
use app\clients\cauriClient\responses\CardGetTokenResponse;
use app\clients\cauriClient\responses\CardProcessRecurringResponse;
use app\clients\cauriClient\responses\CardProcessResponse;
use app\clients\cauriClient\responses\TransactionRefundResponse;
use app\clients\cauriClient\responses\TransactionReverseResponse;
use app\clients\cauriClient\responses\TransactionStatusResponse;
use app\clients\cauriClient\responses\UserResolveResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class CauriClient extends BaseObject
{
    /**
     * @var string
     */
    private $bankUrl;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @param string $bankUrl
     * @param string $publicKey
     * @param string $privateKey
     */
    public function __construct(string $bankUrl, string $publicKey, string $privateKey)
    {
        parent::__construct();

        $this->bankUrl = $bankUrl;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * @param UserResolveRequest $userResolveRequest
     * @return UserResolveResponse
     * @throws GuzzleException
     */
    public function userResolve(UserResolveRequest $userResolveRequest): UserResolveResponse
    {
        $response = $this->doRequest('/user/resolve', [
            'identifier' => $userResolveRequest->getIdentifier(),
            'display_name' => $userResolveRequest->getDisplayName(),
            'email' => $userResolveRequest->getEmail(),
            'phone' => $userResolveRequest->getPhone(),
            'locale' => $userResolveRequest->getLocale(),
            'ip' => $userResolveRequest->getIp(),
        ]);

        return new UserResolveResponse($response['id']);
    }

    /**
     * @param CardGetTokenRequest $cardGetTokenRequest
     * @return CardGetTokenResponse
     * @throws GuzzleException
     */
    public function cardGetToken(CardGetTokenRequest $cardGetTokenRequest): CardGetTokenResponse
    {
        $response = $this->doRequest('/card/getToken', [
            'number' => $cardGetTokenRequest->getNumber(),
            'expiration_month' => $cardGetTokenRequest->getExpirationMonth(),
            'expiration_year' => $cardGetTokenRequest->getExpirationYear(),
            'security_code' => $cardGetTokenRequest->getSecurityCode(),
        ]);

        return new CardGetTokenResponse(
            $response['id'],
            $response['expiresAt']
        );
    }

    /**
     * @param CardProcessRequest $cardProcessRequest
     * @return CardProcessResponse
     * @throws GuzzleException
     */
    public function cardProcess(CardProcessRequest $cardProcessRequest): CardProcessResponse
    {
        $response = $this->doRequest('/card/process', [
            'order_id' => $cardProcessRequest->getOrderId(),
            'description' => $cardProcessRequest->getDescription(),
            'user' => $cardProcessRequest->getUser(),
            'card_token' => $cardProcessRequest->getCardToken(),
            'price' => $cardProcessRequest->getPrice(),
            'currency' => $cardProcessRequest->getCurrency(),
            'acs_return_url' => $cardProcessRequest->getAcsReturnUrl(),
            'recurring' => $cardProcessRequest->getRecurring(),
            'recurring_interval' => $cardProcessRequest->getRecurringInterval(),
            'verify_card' => $cardProcessRequest->getVerifyCard(),
        ]);

        $cardDataObject = new CardDataObject(
            $response['card']['lastFour'],
            $response['card']['mask'],
            $response['card']['type'],
            $response['card']['expirationMonth'],
            $response['card']['expirationYear']
        );

        /** @var AcsDataObject $acsDataObject */
        $acsDataObject = null;
        if (isset($response['acs'])) {
            $acsParametersObject = new AcsParametersObject(
                $response['acs']['parameters']['PaReq'],
                $response['acs']['parameters']['MD'],
                $response['acs']['parameters']['TermUrl']
            );

            $acsDataObject = new AcsDataObject(
                $response['acs']['url'],
                $acsParametersObject
            );
        }

        /** @var RecurringDataObject $recurringDataObject */
        $recurringDataObject = null;
        if (isset($response['recurring'])) {
            $recurringDataObject = new RecurringDataObject(
                $response['recurring']['id'],
                $response['recurring']['interval'],
                $response['recurring']['endsAt']
            );
        }

        return new CardProcessResponse(
            $response['id'],
            $response['success'],
            $response['responseCode'] ?? null,
            $cardDataObject,
            $acsDataObject,
            $recurringDataObject
        );
    }

    /**
     * @param CardAuthenticateRequest $cardAuthenticateRequest
     * @return CardAuthenticateResponse
     * @throws GuzzleException
     */
    public function cardAuthenticate(CardAuthenticateRequest $cardAuthenticateRequest): CardAuthenticateResponse
    {
        $response = $this->doRequest('/card/authenticate', [
            'PaRes' => $cardAuthenticateRequest->getPaRes(),
            'MD' => $cardAuthenticateRequest->getMD(),
        ]);

        return new CardAuthenticateResponse(
            $response['id'],
            $response['success'],
            $response['responseCode'] ?? null
        );
    }

    /**
     * @param CardProcessRecurringRequest $cardProcessRecurringRequest
     * @return CardProcessRecurringResponse
     * @throws GuzzleException
     */
    public function cardProcessRecurring(CardProcessRecurringRequest $cardProcessRecurringRequest): CardProcessRecurringResponse
    {
        $response = $this->doRequest('/card/processRecurring', [
            'order_id' => $cardProcessRecurringRequest->getOrderId(),
            'description' => $cardProcessRecurringRequest->getDescription(),
            'recurring_profile' => $cardProcessRecurringRequest->getRecurringProfile(),
            'price' => $cardProcessRecurringRequest->getPrice(),
            'currency' => $cardProcessRecurringRequest->getCurrency(),
        ]);

        return new CardProcessRecurringResponse(
            $response['id'],
            $response['success'],
            $response['responseCode'] ?? null
        );
    }

    /**
     * @param TransactionStatusRequest $transactionStatusRequest
     * @return TransactionStatusResponse
     * @throws GuzzleException
     */
    public function transactionStatus(TransactionStatusRequest $transactionStatusRequest): TransactionStatusResponse
    {
        $response = $this->doRequest('/transaction/status', [
            'id' => $transactionStatusRequest->getId(),
            'order_id' => $transactionStatusRequest->getOrderId(),
        ]);

        return new TransactionStatusResponse(
            $response['id'],
            $response['order_id'] ?? null,
            $response['description'] ?? null,
            $response['user'],
            $response['price'],
            $response['earned'],
            $response['currency'],
            $response['type'],
            $response['status'],
            $response['error'],
            $response['sandbox'],
            $response['auth_code'] ?? null,
            $response['response_code'] ?? null,
            $response['can_reverse'],
            $response['can_refund'],
            $response['can_partial_refund']
        );
    }

    /**
     * @param TransactionRefundRequest $transactionRefundRequest
     * @return TransactionRefundResponse
     * @throws GuzzleException
     */
    public function transactionRefund(TransactionRefundRequest $transactionRefundRequest): TransactionRefundResponse
    {
        $response = $this->doRequest('/transaction/refund', [
            'id' => $transactionRefundRequest->getId(),
            'order_id' => $transactionRefundRequest->getOrderId(),
            'amount' => $transactionRefundRequest->getAmount(),
            'comment' => $transactionRefundRequest->getComment(),
        ]);

        return new TransactionRefundResponse(
            $response['id'],
            $response['success']
        );
    }

    /**
     * @param TransactionReverseRequest $transactionReverseRequest
     * @return TransactionReverseResponse
     * @throws GuzzleException
     */
    public function transactionReverse(TransactionReverseRequest $transactionReverseRequest): TransactionReverseResponse
    {
        $response = $this->doRequest('/transaction/reverse', [
            'id' => $transactionReverseRequest->getId(),
            'order_id' => $transactionReverseRequest->getOrderId(),
            'comment' => $transactionReverseRequest->getComment(),
        ]);

        return new TransactionReverseResponse(
            $response['id'],
            $response['success']
        );
    }

    /**
     * @param string $endpoint
     * @param array $requestData
     * @return mixed|null
     * @throws GuzzleException
     */
    private function doRequest(string $endpoint, array $requestData)
    {
        $signedRequestData = $this->prepareRequestData($requestData);

        $logData = [
            'Message' => '',
            'Url' => $this->bankUrl . $endpoint,
            'Request Data' => Json::encode($signedRequestData),
        ];

        \Yii::info(ArrayHelper::merge($logData, ['Message' => 'Cauri request start.']));

        try {
            $response = (new Client())->request('POST', $this->bankUrl . $endpoint, [
                'form_params' => $signedRequestData,
            ]);
        } catch (BadResponseException $e) {
            \Yii::error(ArrayHelper::merge($logData, [
                'Message' => "Cauri bad response error. Status code: {$e->getResponse()->getStatusCode()}.",
                'Response Data' => $e->getResponse()->getBody(),
            ]));
            throw $e;
        }

        $responseBody = (string)$response->getBody();

        \Yii::info(ArrayHelper::merge($logData, [
            'Message' => 'Cauri request end.',
            'Response Data' => $responseBody,
        ]));

        return Json::decode($responseBody);
    }

    /**
     * @param array $requestData
     * @return array
     */
    private function prepareRequestData(array $requestData): array
    {
        $requestData = array_filter($requestData, function ($element) {
            return $element !== null;
        });
        $requestData['project'] = $this->publicKey;

        $parameters = array_values($requestData);
        sort($parameters, SORT_STRING);
        $signature = hash_hmac('sha256', join('|', $parameters), $this->privateKey);

        $requestData['signature'] = $signature;

        return $requestData;
    }
}