<?php

namespace app\services\payment\banks;

use app\Api\Client\Client;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CurrencyExchangeRatesResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
use app\services\payment\banks\bank_adapter_responses\IdentInitResponse;
use app\services\payment\banks\traits\WalletoRequestTrait;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\RefundPayException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Yii;

class WalletoBankAdapter implements IBankAdapter
{
    use WalletoRequestTrait;

    /** @var PartnerBankGate */
    protected $gate;
    /** @var Client $api */
    protected $api;
    /** @var String $bankUrl */
    protected $bankUrl;

    public static $bank = 10;
    private const BANK_URL = 'https://api.sandbox.walletto.eu';
    private const KEY_ROOT_PATH = '@app/config/walleto/';

    // Walleto bank statuses
    private const STATUS_NEW = 'new';
    private const STATUS_PREPARED = 'prepared';
    private const STATUS_SUCCESS = 'success';
    private const STATUS_CHARGED = 'charged';
    private const STATUS_REFUNDED = 'refunded';
    private const STATUS_AUTHORIZED = 'authorized';
    private const STATUS_REVERSED = 'reversed';
    public const ERROR_STATUS_MSG = 'Ошибка проверки статуса'; //TODO: create global error handler

    public const BANK_TIMEZONE = 'Europe/Vilnius';

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        $this->bankUrl = self::BANK_URL;
        $apiClientHeader = [
            'Authorization' => $partnerBankGate->Token,
        ];

        $verify = Yii::getAlias(self::KEY_ROOT_PATH . $partnerBankGate->Login . '.pem');
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $verify = false;
        }

        //TODO: move certificates/keys from git directories
        $config = [
            RequestOptions::VERIFY => $verify,
            RequestOptions::CERT => Yii::getAlias(self::KEY_ROOT_PATH . $partnerBankGate->Login . '.pem'),
            RequestOptions::SSL_KEY => Yii::getAlias(self::KEY_ROOT_PATH . $partnerBankGate->Login . '.key'),
            RequestOptions::HEADERS => $apiClientHeader,
        ];
        $infoMessage = sprintf(
            'partnerId=%d bankId=%d',
            $this->gate->PartnerId,
            $this->getBankId()
        );
        $this->api = new Client($config, $infoMessage);
    }

    public function getBankId(): int
    {
        return self::$bank;
    }

    public function confirm(DonePayForm $donePayForm)
    {
        // TODO: Implement confirm() method.
    }

    public function createPay(CreatePayForm $createPayForm): CreatePayResponse
    {
        $action = 'orders/authorize';
        $url = self::BANK_URL . '/' . $action;
        $request = $this->formatCreatePayRequest($createPayForm);
        $createPayResponse = new CreatePayResponse();
        try {
            $response = $this->api->request(
                Client::METHOD_POST,
                $url,
                $request->getAttributes()
            );
        } catch (GuzzleException $e) {
            Yii::error('Walleto payInCreate err: ' . $e->getMessage());
            throw new CreatePayException(
                BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' .  $e->getMessage()
            );
        }
        if (!$response->isSuccess()) {
            Yii::error('Walleto payInCreate err: ' . $response->json('failure_message'));
            $errorMessage = $response->json('failure_message') ?? '';
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);
            return $createPayResponse;
        }
        $responseData = $response->json('orders')[0];
        $createPayResponse->status = BaseResponse::STATUS_DONE;
        $createPayResponse->isNeed3DSRedirect = false;
        $createPayResponse->isNeed3DSVerif = true;
        $createPayResponse->transac = $responseData['id'];
        $createPayResponse->html3dsForm = $responseData['form3d_html'];
        return $createPayResponse;
    }

    /**
     * @param OkPayForm $okPayForm
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    public function checkStatusPay(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $checkStatusPayResponse = new CheckStatusPayResponse();
        $transactionId = $okPayForm->getPaySchet()->ExtBillNumber;
        $url = self::BANK_URL . '/orders/' . $transactionId;
        try {
            $response = $this->api->request(
                Client::METHOD_GET,
                $url,
                []
            );
            if (!$response->isSuccess()) {
                Yii::error('Walleto checkStatusPay err: ' . $response->json('failure_message'));
                $errorMessage = $response->json('failure_message') ?? self::ERROR_STATUS_MSG;
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                $checkStatusPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);
                return $checkStatusPayResponse;
            }
            $responseData = $response->json('orders');
            $checkStatusPayResponse->status = $this->convertStatus($responseData[0]['status']);
            $checkStatusPayResponse->message = '';
        } catch (GuzzleException $e) {
            Yii::error(' Walleto checkStatusPay err:' . $e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' . $e->getMessage()
            );
        }
        return $checkStatusPayResponse;
    }

    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        // TODO: Implement recurrentPay() method.
    }

    public function refundPay(RefundPayForm $refundPayForm)
    {
        $refundPayResponse = new RefundPayResponse();

        $paySchet = $refundPayForm->paySchet;
        if($paySchet->Status != PaySchet::STATUS_DONE) {
            throw new RefundPayException('Невозможно отменить незавершенный платеж');
        }

        $uri = '/orders/' . $paySchet->ExtBillNumber . '/cancel';
        if($paySchet->DateCreate < Carbon::now()->startOfDay()->timestamp) {
            $uri = '/orders/' . $paySchet->ExtBillNumber . '/refund';
        }

        try {
            $response = $this->api->request(
                Client::METHOD_PUT,
                self::BANK_URL . $uri,
                [
                    'amount' => $refundPayForm->paySchet->getSummFull() / 100,
                ]
            );
            if (!$response->isSuccess()) {
                $errorMessage = $response->json('failure_message') ?? self::ERROR_STATUS_MSG;
                $refundPayResponse->status = BaseResponse::STATUS_ERROR;
                $refundPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);
                return $refundPayResponse;
            }
            $responseData = $response->json('orders');
            $requestStatus = $this->convertStatus($responseData[0]['status']);
            if($requestStatus == BaseResponse::STATUS_CANCEL) {
                $refundPayResponse->status = BaseResponse::STATUS_DONE;
            } else {
                $refundPayResponse->status = $this->convertStatus($responseData[0]['status']);
            }

            $refundPayResponse->message = '';
        } catch (GuzzleException $e) {
            Yii::error(' Walleto refundPay err:' . $e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' . $e->getMessage()
            );
        }

        return $refundPayResponse;
    }

    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        // TODO: Implement outCardPay() method.
    }

    public function getAftMinSum()
    {
        // TODO: Implement getAftMinSum() method.
    }

    public function getBalance(GetBalanceRequest $getBalanceForm)
    {
        // TODO: Implement getBalance() method.
    }

    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        // TODO: Implement transferToAccount() method.
    }

    /**
     * @param string $status
     * @return int
     */
    public function convertStatus(string $status): int
    {
        switch ($status) {
            case self::STATUS_PREPARED:
            case self::STATUS_NEW:
                return BaseResponse::STATUS_CREATED;
            case self::STATUS_SUCCESS:
            case self::STATUS_CHARGED:
                return BaseResponse::STATUS_DONE;
            case self::STATUS_REFUNDED:
                return BaseResponse::STATUS_CANCEL;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @inheritDoc
     */
    public function identInit(Ident $ident)
    {
        // TODO: Implement identInit() method.
    }

    /**
     * @inheritDoc
     */
    public function identGetStatus(Ident $ident)
    {
        // TODO: Implement identGetStatus() method.
    }

    /**
     * @return CurrencyExchangeRatesResponse
     * @throws BankAdapterResponseException
     */
    public function currencyExchangeRates(): CurrencyExchangeRatesResponse
    {
        $url = self::BANK_URL . '/exchange_rates/';
        $date = Carbon::now(self::BANK_TIMEZONE)->format('Y-m-d'); // сегодняшняя дата в формате 2021-06-30

        $currencyExchangeRatesResponse = new CurrencyExchangeRatesResponse();

        try {
            $response = $this->api->request(
                Client::METHOD_GET,
                $url,
                ['date' => $date]
            );
        } catch (GuzzleException $e) {
            Yii::error('Walleto currencyExchangeRates err: ' . $e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::setErrorMsg($e->getMessage())
            );
        }

        if (!$response->isSuccess()) {
            Yii::error('Walleto currencyExchangeRates err: ' . $response->json());
            $errorMessage = $response->json();
            $currencyExchangeRatesResponse->status = BaseResponse::STATUS_ERROR;
            $currencyExchangeRatesResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);
            return $currencyExchangeRatesResponse;
        }

        $currencyExchangeRatesResponse->status = BaseResponse::STATUS_DONE;
        $currencyExchangeRatesResponse->exchangeRates = $response->json('exchange_rates');
        return $currencyExchangeRatesResponse;
    }
}
