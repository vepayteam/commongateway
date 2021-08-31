<?php

namespace app\services\payment\helpers;

use app\Api\Client\ClientResponse;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CancelPayResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\decta\OutCardTransactionResponse;
use app\services\payment\banks\bank_adapter_responses\decta\RefundPayResponse;
use app\services\payment\banks\DectaAdapter;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\decta\CreatePayRequest;
use app\services\payment\forms\decta\CreatePaySecondStepRequest;
use app\services\payment\forms\decta\OutCardPayRequest;
use app\services\payment\forms\decta\OutCardTransactionRequest;
use app\services\payment\forms\decta\payin\Client as CreatePayClient;
use app\services\payment\forms\decta\payout\Client as OutCardPayClient;
use app\services\payment\forms\decta\RefundPayRequest;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\RefundPayForm;
use Yii;

/**
 * Class DectaHelper
 */
class DectaHelper
{
    public const ERROR_RESPONSE_TOP_KEY = '__all__';

    /**
     * @param CreatePayForm $createPayForm
     *
     * @return CreatePayRequest
     * @throws CreatePayException
     */
    public static function handlePayRequest(CreatePayForm $createPayForm): CreatePayRequest
    {
        $paySchet = $createPayForm->getPaySchet();

        if ($paySchet === null) {
            throw new CreatePayException('Invalid paySchet');
        }

        $paymentRequest = new CreatePayRequest();
        $paymentRequest->client = new CreatePayClient();
        $paymentRequest->client->email = $paySchet->getUserEmail();
        $paymentRequest->total = $paySchet->getSummFull();
        $paymentRequest->products = [
            [
                'price' => $paySchet->getSummFull(),
                'title' => DectaAdapter::PRODUCT_TITLE,
            ],
        ];
        $paymentRequest->response_type = 'minimal';
        $paymentRequest->success_redirect = $paySchet->getOrderdoneUrl();
        $paymentRequest->failure_redirect = $paySchet->getOrderfailUrl();

        return $paymentRequest;
    }

    /**
     * @param CreatePayForm $createPayForm
     *
     * @return CreatePaySecondStepRequest
     * @throws CreatePayException
     */
    public static function handlePaySecondStepRequest(CreatePayForm $createPayForm): CreatePaySecondStepRequest
    {
        $paySchet = $createPayForm->getPaySchet();

        if ($paySchet === null) {
            throw new CreatePayException('Invalid paySchet');
        }

        $paymentSecondStepRequest = new CreatePaySecondStepRequest();

        $paymentSecondStepRequest->cardholder_name = $createPayForm->CardHolder;
        $paymentSecondStepRequest->card_number = $createPayForm->CardNumber;
        $paymentSecondStepRequest->exp_month = (int) $createPayForm->CardMonth;
        $paymentSecondStepRequest->exp_year = (int) $createPayForm->CardYear;
        $paymentSecondStepRequest->csc = $createPayForm->CardCVC;

        return $paymentSecondStepRequest;
    }

    /**
     * @param RefundPayForm $refundPayForm
     *
     * @return RefundPayRequest
     */
    public static function handleRefundPayRequest(RefundPayForm $refundPayForm): RefundPayRequest
    {
        $refundPayRequest = new RefundPayRequest();
        $refundPayRequest->amount = $refundPayForm->paySchet->getSummFull();

        return $refundPayRequest;
    }

    /**
     * @param OutCardPayForm $outCardPayForm
     *
     * @return OutCardPayRequest
     */
    public static function handleOutCardPayRequest(OutCardPayForm $outCardPayForm): OutCardPayRequest
    {
        $request = new OutCardPayRequest();

        $request->amount = $outCardPayForm->amount;

        $request->client = new OutCardPayClient();
        $request->client->phone = $outCardPayForm->phone;
        $request->client->first_name = $outCardPayForm->fullname;
        $request->client->country = $outCardPayForm->countryOfCitizenship;

        return $request;
    }

    /**
     * @param ClientResponse $response
     *
     * @return CreatePayResponse
     */
    public static function handlePayResponse(ClientResponse $response): CreatePayResponse
    {
        $payResponse = new CreatePayResponse();

        if (!$response->isSuccess()) {
            $errorMessage = self::getErrorMessage($response);
            Yii::error(DectaAdapter::ERROR_CREATE_PAY_MSG.': '.$errorMessage);
            $payResponse->status = BaseResponse::STATUS_ERROR;
            $payResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);

            return $payResponse;
        }

        $responseData = $response->json();

        $result = [];
        $result['status'] = BaseResponse::STATUS_DONE;
        if (array_key_exists('threed_check_url', $responseData)) {
            $result['isNeed3DSRedirect'] = true;
            $result['url'] = $responseData['threed_check_url'];
        } else {
            $result['isNeed3DSRedirect'] = false;
            $result['isNeed3DSVerif'] = false;
        }

        $payResponse->fill(array_merge($responseData, $result));

        return $payResponse;
    }

    /**
     * @param ClientResponse $response
     *
     * @return CheckStatusPayResponse
     */
    public static function handleCheckStatusPayResponse(ClientResponse $response): CheckStatusPayResponse
    {
        $checkStatusPayResponse = new CheckStatusPayResponse();

        if (!$response->isSuccess()) {
            $errorMessage = self::getErrorMessage($response);
            Yii::error(DectaAdapter::ERROR_STATUS_MSG.': '.$errorMessage);
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);

            return $checkStatusPayResponse;
        }

        $checkStatusPayResponse->status = self::convertStatus($response->json('status'));
        $checkStatusPayResponse->message = '';

        return $checkStatusPayResponse;
    }

    /**
     * @param string $status
     *
     * @return int
     */
    public static function convertStatus(string $status): int
    {
        switch ($status) {
            case DectaAdapter::STATUS_PREPARED:
            case DectaAdapter::STATUS_NEW:
                return BaseResponse::STATUS_CREATED;
            case DectaAdapter::STATUS_SUCCESS:
            case DectaAdapter::STATUS_PAID:
                return BaseResponse::STATUS_DONE;
            case DectaAdapter::STATUS_REFUNDED:
                return BaseResponse::STATUS_CANCEL;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @param ClientResponse $response
     *
     * @return RefundPayResponse
     */
    public static function handleRefundPayResponse(ClientResponse $response): RefundPayResponse
    {
        $refundPayResponse = new RefundPayResponse();

        if (!$response->isSuccess()) {

            $errorMessage = self::getErrorMessage($response);

            Yii::error(DectaAdapter::ERROR_REFUND_MSG.': '.$errorMessage);

            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
            $refundPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);

            return $refundPayResponse;
        }

        $refundPayResponse->status = BaseResponse::STATUS_DONE;
        $refundPayResponse->message = json_encode($response->json());

        return $refundPayResponse;
    }

    /**
     * @param ClientResponse $response
     *
     * @return CancelPayResponse
     */
    public static function handleCancelPayResponse(ClientResponse $response): CancelPayResponse
    {
        $cancelPayResponse = new CancelPayResponse();

        if (!$response->isSuccess()) {

            $errorMessage = self::getErrorMessage($response);

            Yii::error(DectaAdapter::ERROR_CANCEL_MSG.': '.$errorMessage);

            $cancelPayResponse->status = BaseResponse::STATUS_ERROR;
            $cancelPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);

            return $cancelPayResponse;
        }

        $cancelPayResponse->status = BaseResponse::STATUS_DONE;
        $cancelPayResponse->orderData = $response->json();

        return $cancelPayResponse;
    }

    /**
     * @param ClientResponse $response
     *
     * @return OutCardTransactionResponse
     */
    public static function handleOutCardTransactionResponse(ClientResponse $response): OutCardTransactionResponse
    {
        $outCardTransactionResponse = new OutCardTransactionResponse();

        if (!$response->isSuccess()) {

            $errorMessage = self::getErrorMessage($response);

            Yii::error(DectaAdapter::ERROR_REFUND_MSG.': '.$errorMessage);

            $outCardTransactionResponse->status = BaseResponse::STATUS_ERROR;
            $outCardTransactionResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);

            return $outCardTransactionResponse;
        }

        $outCardTransactionResponse->status = BaseResponse::STATUS_DONE;
        $outCardTransactionResponse->message = json_encode($response->json());

        return $outCardTransactionResponse;
    }

    /**
     * @param OutCardPayForm $outCardPayForm
     *
     * @return OutCardTransactionRequest
     */
    public static function handleOutCardTransactionRequest(OutCardPayForm $outCardPayForm): OutCardTransactionRequest
    {
        $outCardTransactionRequest = new OutCardTransactionRequest();

        $outCardTransactionRequest->card_number2 = $outCardPayForm->cardnum;
        $outCardTransactionRequest->payment_cardholder_name = $outCardPayForm->cardHolderName;

        return $outCardTransactionRequest;
    }

    /**
     * @param ClientResponse $response
     *
     * @return string
     */
    public static function getErrorMessage(ClientResponse $response): string
    {
        $responseData = $response->json();

        return $responseData[self::ERROR_RESPONSE_TOP_KEY][0]['message'] ?? '';
    }
}
