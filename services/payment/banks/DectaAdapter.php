<?php

namespace app\services\payment\banks;

use app\Api\Client\AbstractClient;
use app\Api\Client\Client;
use app\Api\Client\ClientResponse;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CancelPayResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\decta\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\decta\OutCardTransactionResponse;
use app\services\payment\banks\bank_adapter_responses\decta\RefundPayResponse;
use app\services\payment\banks\exceptions\DectaApiUrlException;
use app\services\payment\banks\exceptions\InvalidBankActionException;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CancelPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\helpers\DectaHelper;
use app\services\payment\models\PartnerBankGate;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Yii;

/**
 * Class DectaAdapter
 */
class DectaAdapter implements IBankAdapter
{
    public const AFT_MIN_SUM = 120000;

    /** @var PartnerBankGate $gate */
    protected $gate;
    /** @var Client $api */
    protected $api;
    /** @var string $apiUrl */
    protected $apiUrl;

    public static $bank = 12;
    private const API_URL = 'https://gate.decta.com/api/v0.6';

    public const STATUS_NEW = 'issued';
    public const STATUS_PREPARED = 'in_progress';
    public const STATUS_SUCCESS = 'received';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PAID = 'paid';
    public const PRODUCT_TITLE = 'Payment';
    public const ERROR_CREATE_PAY_MSG = 'Decta create pay error'; //TODO: create global error handler
    public const ERROR_STATUS_MSG = 'Decta check pay status error';
    public const ERROR_REFUND_MSG = 'Decta refund pay error';
    public const ERROR_CANCEL_MSG = 'Decta cancel pay error';
    public const ERROR_OUT_CARD_PAY_MSG = 'Decta out card pay error';
    public const ERROR_METHOD_NOT_ALLOWED_MSG = 'Method not allowed';
    public const INVALID_DECTA_API_URL = 'Invalid Decta response API URL';

    private const ACTIONS = [
        'cancel_pay' => 'orders/{payment_id}/cancel',
        'check_pay_status' => 'orders/{payment_id}',
        'out_pay' => 'orders/init_sdwo_payout',
        'pay' => 'orders/',
        'refund_pay' => 'orders/{payment_id}/refund',
    ];

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        $this->apiUrl = (Yii::$app->params['dectaApiUrl'] ?? 'https://gate.decta.com').'/api/v0.6';
        $apiClientHeader = [
            'Authorization' => $partnerBankGate->Token,
        ];
        $config = [
            RequestOptions::HEADERS => $apiClientHeader,
            RequestOptions::PROXY => Yii::$app->params['dectaProxy'],
        ];
        $infoMessage = sprintf(
            'partnerId=%d bankId=%d',
            $this->gate->PartnerId,
            $this->getBankId()
        );
        $this->api = new Client($config, $infoMessage);
    }

    /**
     * @return int
     */
    public function getBankId(): int
    {
        return self::$bank;
    }

    /**
     * @param CreatePayForm $createPayForm
     *
     * @return CreatePayResponse
     * @throws BankAdapterResponseException
     * @throws CreatePayException
     */
    public function createPay(CreatePayForm $createPayForm): CreatePayResponse
    {
        $url = $this->getRequestUrl('pay');

        try {
            $requestData = DectaHelper::handlePayRequest($createPayForm)->toArray();
            $requestData['terminal_processing_id'] = $this->gate->AdvParam_1;
            $requestData['currency'] = $this->gate->currency->Code;

            $response = $this->api->request(AbstractClient::METHOD_POST, $url, $requestData);
            if (!$response->isSuccess()) {
                $this->handleError(new BankAdapterResponseException('response is not success. Data: '.json_encode($requestData)), self::ERROR_CREATE_PAY_MSG);
            }
            return $this->createPaySecondStep($createPayForm, $response);
        } catch (GuzzleException $e) {
            $this->handleError(new BankAdapterResponseException('response is not success. Message: '.$e->getMessage()), self::ERROR_CREATE_PAY_MSG);
        }
    }

    /**
     * @param CreatePayForm $createPayForm
     * @param ClientResponse $createPayResponse
     *
     * @return CreatePayResponse
     * @throws CreatePayException
     * @throws BankAdapterResponseException
     */
    public function createPaySecondStep(CreatePayForm $createPayForm, ClientResponse $createPayResponse): CreatePayResponse
    {
        $createPaySecondStepUrl = $this->getCreatePaySecondStepUrl($createPayResponse);

        if ($createPaySecondStepUrl === null) {
            $this->handleError(new BankAdapterResponseException('api_do_url not found'), self::ERROR_CREATE_PAY_MSG);
        }

        try {
            $createPaySecondStepResponse = $this->api->request(
                AbstractClient::METHOD_POST,
                $createPaySecondStepUrl,
                DectaHelper::handlePaySecondStepRequest($createPayForm)->toArray()
            );
            return DectaHelper::handlePayResponse($createPayResponse, $createPaySecondStepResponse);
        } catch (GuzzleException $e) {
            $this->handleError(new BankAdapterResponseException($e->getMessage()), self::ERROR_CREATE_PAY_MSG);
        }
    }

    /**
     * @param OkPayForm $okPayForm
     *
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    public function checkStatusPay(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $url = $this->getRequestUrl('check_pay_status', [
            'payment_id' => $okPayForm->getPaySchet()->ExtBillNumber
        ]);

        try {
            $response = $this->api->request(AbstractClient::METHOD_GET, $url, []);
            return DectaHelper::handleCheckStatusPayResponse($response);
        } catch (GuzzleException $e) {
            $this->handleError(new BankAdapterResponseException($e->getMessage()), self::ERROR_STATUS_MSG);
        }
    }

    /**
     * @param RefundPayForm $refundPayForm
     *
     * @return RefundPayResponse
     * @throws BankAdapterResponseException
     */
    public function refundPay(RefundPayForm $refundPayForm): RefundPayResponse
    {
        $url = $this->getRequestUrl('refund_pay', [
            'payment_id' => $refundPayForm->paySchet->ExtBillNumber
        ]);

        try {
            $response = $this->api->request(
                AbstractClient::METHOD_POST,
                $url,
                DectaHelper::handleRefundPayRequest($refundPayForm)->toArray()
            );
            return DectaHelper::handleRefundPayResponse($response);
        } catch (GuzzleException $e) {
            $this->handleError(new BankAdapterResponseException($e->getMessage()), self::ERROR_REFUND_MSG);
        }
    }

    /**
     * @param OutCardPayForm $outCardPayForm
     *
     * @return OutCardPayResponse
     * @throws BankAdapterResponseException
     * @throws DectaApiUrlException
     */
    public function outCardPay(OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        $url = $this->getRequestUrl('out_pay');

        try {
            $response = $this->api->request(
                AbstractClient::METHOD_POST,
                $url,
                DectaHelper::handleOutCardPayRequest($outCardPayForm)->toArray()
            );
            return $this->handleOutCardPayResponse($response, $outCardPayForm);
        } catch (GuzzleException $e) {
            $this->handleError(new BankAdapterResponseException($e->getMessage()), self::ERROR_OUT_CARD_PAY_MSG);
        }
    }

    /**
     * @param CancelPayForm $cancelPayForm
     *
     * @return CancelPayResponse
     * @throws BankAdapterResponseException
     */
    public function cancelPay(CancelPayForm $cancelPayForm): CancelPayResponse
    {
        $url = $this->getRequestUrl('cancel_pay', [
            'payment_id' => $cancelPayForm->getPaySchet()->ExtBillNumber
        ]);

        try {
            $response = $this->api->request(AbstractClient::METHOD_POST, $url, []);
            return DectaHelper::handleCancelPayResponse($response);
        } catch (GuzzleException $e) {
            $this->handleError(new BankAdapterResponseException($e->getMessage()), self::ERROR_CANCEL_MSG);
        }
    }

    /**
     * @param DonePayForm $donePayForm
     *
     * @return ConfirmPayResponse
     */
    public function confirm(DonePayForm $donePayForm): ConfirmPayResponse
    {
        $confirmPayResponse = new ConfirmPayResponse();
        $confirmPayResponse->status = BaseResponse::STATUS_DONE;
        return $confirmPayResponse;
    }

    /**
     * @param string $action
     * @param array $placeholders
     *
     * @return string
     */
    protected function getRequestUrl(string $action, array $placeholders = []): string
    {
        if (!array_key_exists($action, self::ACTIONS)) {
            throw new InvalidBankActionException(
                'Invalid action. Allowed: '.implode(', ', array_keys(self::ACTIONS))
            );
        }

        $action = self::ACTIONS[$action];

        if (!empty($placeholders)) {

            $placeholderKeys = array_keys($placeholders);

            foreach ($placeholderKeys as $key) {
                $action = str_replace('{'.$key.'}', $placeholders[$key], $action);
            }
        }

        return $this->getBaseUrl().'/'.$action;
    }

    /**
     * @return string|null
     */
    protected function getBaseUrl(): ?string
    {
        return $this->apiUrl;
    }

    /**
     * @param ClientResponse $response
     * @param OutCardPayForm $outCardPayForm
     *
     * @return OutCardPayResponse
     * @throws BankAdapterResponseException
     * @throws DectaApiUrlException
     */
    protected function handleOutCardPayResponse(ClientResponse $response, OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        $outCardPayResponse = new OutCardPayResponse();

        if (!$response->isSuccess()) {
            $errorMessage = DectaHelper::getErrorMessage($response);
            Yii::error(self::ERROR_OUT_CARD_PAY_MSG.': '.$errorMessage);
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);

            return $outCardPayResponse;
        }

        if (!is_string($outCardPayResponse->api_do_url) || $outCardPayResponse->api_do_url === '') {
            throw new DectaApiUrlException(self::INVALID_DECTA_API_URL);
        }

        $outCardPayResponse->status = BaseResponse::STATUS_DONE;
        $outCardPayResponse->data = $response->json();
        $outCardPayResponse->transaction_data =
            $this->processOutCardTransaction($outCardPayResponse->api_do_url, $outCardPayForm)->toArray();

        return $outCardPayResponse;
    }

    /**
     * @param string $apiDoUrl
     * @param OutCardPayForm $outCardPayForm
     *
     * @return OutCardTransactionResponse
     * @throws BankAdapterResponseException
     */
    protected function processOutCardTransaction(string $apiDoUrl, OutCardPayForm $outCardPayForm): OutCardTransactionResponse
    {
        try {
            $response = $this->api->request(
                AbstractClient::METHOD_POST,
                $apiDoUrl,
                DectaHelper::handleOutCardTransactionRequest($outCardPayForm)->toArray()
            );
            return DectaHelper::handleOutCardTransactionResponse($response);
        } catch (GuzzleException $e) {
            $this->handleError(new BankAdapterResponseException($e->getMessage()), self::ERROR_OUT_CARD_PAY_MSG);
        }
    }

    /**
     * @param ClientResponse $createPayResponse
     *
     * @return string|null
     */
    protected function getCreatePaySecondStepUrl(ClientResponse $createPayResponse): ?string
    {
        return $createPayResponse->json('api_do_url');
    }

    /**
     * @param Exception $e
     * @param string $title
     *
     * @throws BankAdapterResponseException
     */
    protected function handleError(Exception $e, string $title = 'Unknown error'): void
    {
        Yii::error($title.': '.$e->getMessage());
        throw new BankAdapterResponseException(
            BankAdapterResponseException::setErrorMsg($e->getMessage())
        );
    }

    /**
     * @param AutoPayForm $autoPayForm
     *
     * @return void
     * @throws GateException
     */
    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        $this->throwGateException();
    }

    /**
     * @return int
     */
    public function getAftMinSum(): int
    {
        return self::AFT_MIN_SUM;
    }

    /**
     * @throws GateException
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest)
    {
        $this->throwGateException();
    }

    /**
     * @throws GateException
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        $this->throwGateException();
    }

    /**
     * @inheritDoc
     * @throws GateException
     */
    public function identInit(Ident $ident)
    {
        $this->throwGateException();
    }

    /**
     * @inheritDoc
     * @throws GateException
     */
    public function identGetStatus(Ident $ident)
    {
        $this->throwGateException();
    }

    /**
     * @throws GateException
     */
    public function currencyExchangeRates()
    {
        $this->throwGateException();
    }

    /**
     * @throws GateException
     */
    private function throwGateException(): void
    {
        throw new GateException(self::ERROR_METHOD_NOT_ALLOWED_MSG);
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        // TODO: Implement sendP2p() method.
    }
}
