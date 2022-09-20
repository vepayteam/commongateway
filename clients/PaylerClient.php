<?php

namespace app\clients;

use app\clients\paylerClient\PaylerException;
use app\clients\paylerClient\requests\ChallengeCompleteRequest;
use app\clients\paylerClient\requests\CreditMerchantRequest;
use app\clients\paylerClient\requests\GetStatusRequest;
use app\clients\paylerClient\requests\PayMerchantRequest;
use app\clients\paylerClient\requests\RefundRequest;
use app\clients\paylerClient\requests\RepeatPayRequest;
use app\clients\paylerClient\requests\Send3dsRequest;
use app\clients\paylerClient\requests\ThreeDsMethodCompleteRequest;
use app\clients\paylerClient\responses\CreditMerchantResponse;
use app\clients\paylerClient\responses\ErrorResponse;
use app\clients\paylerClient\responses\GetStatusResponse;
use app\clients\paylerClient\responses\PayMerchantResponse;
use app\clients\paylerClient\responses\RefundResponse;
use app\clients\paylerClient\responses\RepeatPayResponse;
use app\clients\paylerClient\responses\Send3dsResponse;
use app\clients\paylerClient\responses\ThreeDsMethodCompleteResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class PaylerClient extends BaseObject
{
    /**
     * @var string
     */
    private $bankUrl;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $password;

    public function __construct(string $bankUrl, string $key, string $password)
    {
        parent::__construct();

        $this->bankUrl = $bankUrl;
        $this->key = $key;
        $this->password = $password;
    }

    /**
     * @param PayMerchantRequest $request
     * @return PayMerchantResponse
     * @throws PaylerException
     */
    public function payMerchant(PayMerchantRequest $request): PayMerchantResponse
    {
        $response = $this->doRequest('/mapi/v1/Pay', [
            'order_id' => $request->orderId,
            'currency' => $request->currency,
            'amount' => $request->amount,
            'card_number' => $request->cardNumber,
            'card_holder' => $request->cardHolder,
            'expired_year' => $request->expiredYear,
            'expired_month' => $request->expiredMonth,
            'secure_code' => $request->secureCode,
            'lang' => $request->lang,
            'email' => $request->email,
            'userdata' => $request->userData,
            'recurrent' => $request->recurrent,
            'payer_ip' => $request->payerIp,
            'browserAccept' => $request->browserAccept,
            'browserLanguage' => $request->browserLanguage,
            'browserUserAgent' => $request->browserUserAgent,
            'browserJavaEnabled' => $request->browserJavaEnabled,
            'browserJavascriptEnabled' => $request->browserJavaScriptEnabled,
            'browserScreenHeight' => $request->browserScreenHeight,
            'browserScreenWidth' => $request->browserScreenWidth,
            'browserColorDepth' => $request->browserColorDepth,
            'browserTZ' => $request->browserTZ,
            'threeDsNotificationUrl' => $request->threeDsNotificationUrl,
        ]);

        return new PayMerchantResponse(
            $response['order_id'],
            $response['amount'],
            $response['auth_type'],
            $response['recurrent_template_id'] ?? null,
            $response['card_id'] ?? null,
            $response['card_status'] ?? null,
            $response['card_number'] ?? null,
            $response['card_holder'] ?? null,
            $response['expired_month'] ?? null,
            $response['expired_year'] ?? null,
            $response['acs_url'] ?? null,
            $response['md'] ?? null,
            $response['pareq'] ?? null,
            $response['threeDS_server_transID'] ?? null,
            $response['threeDS_method_url'] ?? null,
            $response['creq'] ?? null,
            $response['status'] ?? null
        );
    }

    /**
     * @param ThreeDsMethodCompleteRequest $request
     * @return ThreeDsMethodCompleteResponse
     * @throws PaylerException
     */
    public function threeDsMethodComplete(ThreeDsMethodCompleteRequest $request): ThreeDsMethodCompleteResponse
    {
        $response = $this->doRequest('/mapi/v1/ThreeDsMethodComplete', [
            'threeDs_comp_ind' => $request->threeDsCompInd,
            'threeDS_server_transID' => $request->threeDSServerTransId,
        ]);

        return new ThreeDsMethodCompleteResponse(
            $response['acs_url'] ?? null,
            $response['creq'] ?? null,
            $response['auth_type'],
            $response['amount'],
            $response['recurrent_template_id'] ?? null,
            $response['card_number'] ?? null,
            $response['card_holder'] ?? null,
            $response['expired_year'] ?? null,
            $response['expired_month'] ?? null,
            $response['order_id'],
            $response['status'] ?? null
        );
    }

    /**
     * @param ChallengeCompleteRequest $request
     * @return Send3dsResponse
     * @throws PaylerException
     */
    public function challengeComplete(ChallengeCompleteRequest $request): Send3dsResponse
    {
        $response = $this->doRequest('/mapi/v1/ChallengeComplete', [
            'cres' => $request->cRes,
        ]);

        return new Send3dsResponse(
            $response['auth_type'],
            $response['amount'],
            $response['recurrent_template_id'] ?? null,
            $response['order_id'],
            $response['status'] ?? null
        );
    }

    /**
     * @param Send3dsRequest $request
     * @return Send3dsResponse
     * @throws PaylerException
     */
    public function send3ds(Send3dsRequest $request): Send3dsResponse
    {
        $response = $this->doRequest('/mapi/v1/Send3DS', [
            'PaRes' => $request->paRes,
            'MD' => $request->md,
        ]);

        return new Send3dsResponse(
            $response['auth_type'],
            $response['amount'],
            $response['recurrent_template_id'] ?? null,
            $response['order_id'],
            $response['status'] ?? null
        );
    }

    /**
     * @param GetStatusRequest $request
     * @return GetStatusResponse
     * @throws PaylerException
     */
    public function getStatus(GetStatusRequest $request): GetStatusResponse
    {
        $response = $this->doRequest('/mapi/GetStatus', [
            'order_id' => $request->orderId,
        ]);

        return new GetStatusResponse(
            $response['order_id'],
            $response['amount'],
            $response['status'],
            $response['recurrent_template_id'] ?? null,
            $response['payment_type'] ?? null
        );
    }

    /**
     * @param RefundRequest $request
     * @return RefundResponse
     * @throws PaylerException
     */
    public function refund(RefundRequest $request): RefundResponse
    {
        $response = $this->doRequest('/mapi/Refund', [
            'order_id' => $request->orderId,
            'amount' => $request->amount,
        ], true);

        return new RefundResponse(
            $response['order_id'],
            $response['amount']
        );
    }

    /**
     * @param RepeatPayRequest $request
     * @return RepeatPayResponse
     * @throws PaylerException
     */
    public function repeatPay(RepeatPayRequest $request): RepeatPayResponse
    {
        $response = $this->doRequest('/mapi/v1/RepeatPay', [
            'order_id' => $request->orderId,
            'amount' => $request->amount,
            'recurrent_template_id' => $request->recurrentTemplateId,
            'currency' => $request->currency,
        ]);

        return new RepeatPayResponse(
            $response['order_id'],
            $response['amount'],
            $response['status']
        );
    }

    /**
     * @param CreditMerchantRequest $request
     * @return CreditMerchantResponse
     * @throws PaylerException
     */
    public function creditMerchant(CreditMerchantRequest $request): CreditMerchantResponse
    {
        $response = $this->doRequest('/cmapi/Credit', [
            'order_id' => $request->orderId,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'card_number' => $request->cardNumber,
            'card_holder' => $request->cardHolder,
            'lang' => $request->lang,
            'email' => $request->email,
        ], true);

        return new CreditMerchantResponse(
            $response['order_id'],
            $response['amount'],
            $response['card_holder'] ?? null,
            $response['card_number'] ?? null,
            $response['status']
        );
    }

    /**
     * @param string $endpoint
     * @param array $requestData
     * @param bool $setPassword
     * @return array
     * @throws PaylerException
     */
    private function doRequest(string $endpoint, array $requestData, bool $setPassword = false): array
    {
        $requestData['key'] = $this->key;
        if ($setPassword) {
            $requestData['password'] = $this->password;
        }

        $requestData = array_filter($requestData, function ($item) {
            return $item !== null;
        });

        $logData = [
            'Message' => '',
            'Url' => $this->bankUrl . $endpoint,
            'Request Data' => Json::encode($requestData),
        ];

        \Yii::info(ArrayHelper::merge($logData, ['Message' => 'Payler request start.']));

        try {
            $response = (new Client())->request('POST', $this->bankUrl . $endpoint, [
                'form_params' => $requestData,
            ]);
        } catch (BadResponseException $e) {
            \Yii::error(ArrayHelper::merge($logData, [
                'Message' => "Payler bad response error. Status code: {$e->getResponse()->getStatusCode()}.",
                'Response Data' => (string)$e->getResponse()->getBody(),
            ]));

            $errorResponse = $this->tryParseErrorResponse((string)$e->getResponse()->getBody());

            throw new PaylerException($errorResponse, $e);
        } catch (GuzzleException $e) {
            \Yii::error(ArrayHelper::merge($logData, [
                'Message' => "Payler guzzle exception.",
            ]));

            throw new PaylerException(null, $e);
        }

        $responseBody = (string)$response->getBody();

        \Yii::info(ArrayHelper::merge($logData, [
            'Message' => 'Payler request end.',
            'Response Data' => $responseBody,
        ]));

        return Json::decode($responseBody);
    }

    /**
     * @param string $responseData
     * @return ErrorResponse|null
     */
    private function tryParseErrorResponse(string $responseData): ?ErrorResponse
    {
        try {
            $obj = Json::decode($responseData);
        } catch (InvalidArgumentException $e) {
            return null;
        }

        if (isset($obj['error'])) {
            return new ErrorResponse(
                $obj['error']['code'],
                $obj['error']['message']
            );
        }

        return null;
    }
}