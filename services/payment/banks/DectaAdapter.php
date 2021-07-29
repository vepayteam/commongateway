<?php

namespace app\services\payment\banks;

use app\Api\Client\Client;
use app\Api\Client\ClientResponse;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\decta\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\decta\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\decta\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\decta\OutCardTransactionResponse;
use app\services\payment\banks\bank_adapter_responses\decta\RefundPayResponse;
use app\services\payment\banks\exceptions\DectaApiUrlException;
use app\services\payment\banks\exceptions\InvalidBankActionException;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\helpers\DectaHelper;
use app\services\payment\models\PartnerBankGate;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Yii;

/**
 * Class DectaAdapter
 *
 * @package app\services\payment\banks
 */
class DectaAdapter implements IBankAdapter
{
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
    public const ERROR_STATUS_MSG = 'Check status error'; //TODO: create global error handler
    public const ERROR_REFUND_MSG = 'Refund pay error';
    public const ERROR_CANCEL_MSG = 'Cancel pay error';
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
        $this->apiUrl = self::API_URL;
        $apiClientHeader = [
            'Authorization' => $partnerBankGate->Token,
        ];
        $config = [
            RequestOptions::HEADERS => $apiClientHeader,
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
     */
    public function createPay(CreatePayForm $createPayForm): CreatePayResponse
    {
        $url = $this->getRequestUrl('pay');

        try {
            $response = $this->api->request(
                Client::METHOD_POST,
                $url,
                DectaHelper::handlePayRequest($createPayForm)->fields()
            );
        } catch (GuzzleException $e) {
            Yii::error('Decta create pay error: '.$e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::setErrorMsg($e->getMessage())
            );
        }

        return DectaHelper::handlePayResponse($response);
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
            $response = $this->api->request(Client::METHOD_GET, $url, []);
            $checkStatusPayResponse = DectaHelper::handleCheckStatusPayResponse($response);
        } catch (GuzzleException $e) {
            Yii::error('Decta check status pay error: '.$e->getMessage());
            throw new BankAdapterResponseException(
                self::ERROR_STATUS_MSG.' : '.$e->getMessage()
            );
        }

        return $checkStatusPayResponse;
    }

    /**
     * @param RefundPayForm $refundPayForm
     *
     * @return RefundPayResponse
     * @throws BankAdapterResponseException
     * @throws GuzzleException
     */
    public function refundPay(RefundPayForm $refundPayForm): RefundPayResponse
    {
        $url = $this->getRequestUrl('refund_pay', [
            'payment_id' => $refundPayForm->paySchet->ExtBillNumber
        ]);

        try {
            $response = $this->api->request(
                Client::METHOD_POST,
                $url,
                DectaHelper::handleRefundPayRequest($refundPayForm)->fields()
            );
        } catch (Exception $e) {
            Yii::error('Decta refund pay error: '.$e->getMessage());
            throw new BankAdapterResponseException(self::ERROR_REFUND_MSG.': '.$e->getMessage());
        }

        return DectaHelper::handleRefundPayResponse($response);
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
                Client::METHOD_POST,
                $url,
                DectaHelper::handleOutCardPayRequest($outCardPayForm)->fields()
            );
        } catch (GuzzleException $e) {
            Yii::error('Decta out card pay error: '.$e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::setErrorMsg($e->getMessage())
            );
        }

        return $this->handleOutCardPayResponse($response, $outCardPayForm);
    }

    /**
     * @param string $action
     * @param array  $placeholders
     *
     * @return string
     */
    protected function getRequestUrl(string $action, array $placeholders = []): string
    {
        if (array_key_exists($action, self::ACTIONS) === false) {
            throw new InvalidBankActionException(
                'Invalid action. Allowed: '.implode(', ', array_keys(self::ACTIONS))
            );
        }

        $action = self::ACTIONS[$action];

        if (empty($placeholders) === false) {

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

        if ($response->isSuccess() === false) {
            $errorMessage = DectaHelper::getErrorMessage($response);
            Yii::error('Decta payout error: '.$errorMessage);
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);

            return $outCardPayResponse;
        }

        if((is_string($outCardPayResponse->api_do_url) && $outCardPayResponse->api_do_url !== '') === false) {
            throw new DectaApiUrlException(self::INVALID_DECTA_API_URL);
        }

        $outCardPayResponse->status = BaseResponse::STATUS_DONE;
        $outCardPayResponse->data = $response->json();
        $outCardPayResponse->transaction_data =
            $this->processOutCardTransaction($outCardPayResponse->api_do_url, $outCardPayForm)->fields();

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
                Client::METHOD_POST,
                $apiDoUrl,
                DectaHelper::handleOutCardTransactionRequest($outCardPayForm)->fields()
            );
        } catch (GuzzleException $e) {
            Yii::error('Decta create pay error: '.$e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::setErrorMsg($e->getMessage())
            );
        }

        return DectaHelper::handleOutCardTransactionResponse($response);
    }

    /**
     * @param DonePayForm $donePayForm
     *
     * @return void
     * @throws GateException
     */
    public function confirm(DonePayForm $donePayForm)
    {
        $this->throwGateException();
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
     * @throws GateException
     */
    public function getAftMinSum()
    {
        $this->throwGateException();
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

    /**
     * @inheritDoc
     * @throws BankAdapterResponseException|GuzzleException
     */
    public function cancelPay(CancelPayForm $cancelPayForm): CancelPayResponse
    {
        $url = $this->getRequestUrl('cancel_pay', [
            'payment_id' => $cancelPayForm->paySchet->ExtBillNumber
        ]);

        try {
            $response = $this->api->request(Client::METHOD_POST, $url, []);
        } catch (Exception $e) {
            Yii::error('Decta cancel pay error: '.$e->getMessage());
            throw new BankAdapterResponseException(self::ERROR_CANCEL_MSG.': '.$e->getMessage());
        }

        return DectaHelper::handleCancelPayResponse($response);
    }
}
