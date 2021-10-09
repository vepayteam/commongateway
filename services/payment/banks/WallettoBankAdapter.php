<?php

namespace app\services\payment\banks;

use app\Api\Client\Client;
use app\Api\Client\ClientResponse;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CurrencyExchangeRatesResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\traits\WallettoRequestTrait;
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
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\helpers\Json;

class WallettoBankAdapter implements IBankAdapter
{
    use WallettoRequestTrait;

    /** @var PartnerBankGate */
    protected $gate;
    /** @var Client $api */
    protected $api;

    public static $bank = 10;
    private const BANK_URL = 'https://api.walletto.eu';
    private const BANK_TEST_URL = 'https://api.sandbox.walletto.eu';
    private const KEY_ROOT_PATH = '@app/config/walletto/';

    // Walletto bank statuses
    private const STATUS_NEW = 'new';
    private const STATUS_PREPARED = 'prepared';
    private const STATUS_SUCCESS = 'success';
    private const STATUS_CHARGED = 'charged';
    private const STATUS_REFUNDED = 'refunded';
    private const STATUS_AUTHORIZED = 'authorized';
    private const STATUS_REVERSED = 'reversed';

    public const ERROR_STATUS_MSG = 'Ошибка проверки статуса'; //TODO: create global error handler
    public const ERROR_EXCEPTION_MSG = 'Не удалось связаться с провайдером';

    public const BANK_TIMEZONE = 'Europe/Vilnius';

    /**
     * @return string
     */
    protected function bankUrl(): string
    {
        if (Yii::$app->params['DEVMODE'] === 'Y' || Yii::$app->params['TESTMODE'] === 'Y') {
            return self::BANK_TEST_URL;
        } else {
            return self::BANK_URL;
        }
    }

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        $apiClientHeader = [
            'Authorization' => 'Basic ' . base64_encode($partnerBankGate->Login . ':' . $partnerBankGate->Password),
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
        $url = $this->bankUrl() . '/' . $action;
        $request = $this->formatCreatePayRequest($createPayForm);
        $createPayResponse = new CreatePayResponse();
        try {
            $response = $this->api->request(
                Client::METHOD_POST,
                $url,
                $request->getAttributes()
            );
        } catch (GuzzleException $e) {
            Yii::error('Walletto payInCreate err: ' . $e->getMessage());
            throw new CreatePayException(
                BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' . self::ERROR_EXCEPTION_MSG
            );
        }
        if (!$response->isSuccess()) {
            $failureMessage = self::getFailureMessage($response);
            Yii::error('Walletto payInCreate err: ' . $failureMessage);
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = BankAdapterResponseException::setErrorMsg($failureMessage);
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
        $url = $this->bankUrl() . '/orders/' . $transactionId;
        try {
            $response = $this->api->request(
                Client::METHOD_GET,
                $url,
                []
            );
            if (!$response->isSuccess()) {
                $failureMessage = self::getFailureMessage($response);
                Yii::error('Walletto checkStatusPay err: ' . $failureMessage);
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                $checkStatusPayResponse->message = BankAdapterResponseException::setErrorMsg($failureMessage);
                return $checkStatusPayResponse;
            }
            $responseData = $response->json('orders');
            $checkStatusPayResponse->status = $this->convertStatus($responseData[0]['status']);
            $checkStatusPayResponse->message = $responseData[0]['failure_message'] ?? '';
        } catch (GuzzleException $e) {
            Yii::error(' Walletto checkStatusPay err:' . $e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' . self::ERROR_EXCEPTION_MSG
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
        if ($paySchet->Status != PaySchet::STATUS_DONE) {
            throw new RefundPayException('Невозможно отменить незавершенный платеж');
        }

        $uri = '/orders/' . $paySchet->ExtBillNumber . '/cancel';
        if ($paySchet->DateCreate < Carbon::now()->startOfDay()->timestamp) {
            $uri = '/orders/' . $paySchet->ExtBillNumber . '/refund';
        }

        try {
            $response = $this->api->request(
                Client::METHOD_PUT,
                $this->bankUrl() . $uri,
                [
                    'amount' => $refundPayForm->paySchet->getSummFull() / 100,
                ]
            );
            if (!$response->isSuccess()) {
                $refundPayResponse->status = BaseResponse::STATUS_ERROR;
                $refundPayResponse->message = BankAdapterResponseException::setErrorMsg(self::getFailureMessage($response));
                return $refundPayResponse;
            }
            $responseData = $response->json('orders');
            $requestStatus = $this->convertStatus($responseData[0]['status']);
            if ($requestStatus == BaseResponse::STATUS_CANCEL) {
                $refundPayResponse->status = BaseResponse::STATUS_DONE;
            } else {
                $refundPayResponse->status = $this->convertStatus($responseData[0]['status']);
            }

            $refundPayResponse->message = '';
        } catch (GuzzleException $e) {
            Yii::error(' Walletto refundPay err:' . $e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' . self::ERROR_EXCEPTION_MSG
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
        $url = $this->bankUrl() . '/exchange_rates/';
        $date = Carbon::now(self::BANK_TIMEZONE)->format('Y-m-d'); // сегодняшняя дата в формате 2021-06-30

        $currencyExchangeRatesResponse = new CurrencyExchangeRatesResponse();

        try {
            $response = $this->api->request(
                Client::METHOD_GET,
                $url,
                ['date' => $date]
            );
        } catch (GuzzleException $e) {
            Yii::error('Walletto currencyExchangeRates err: ' . $e->getMessage());
            throw new BankAdapterResponseException(
                BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' . self::ERROR_EXCEPTION_MSG
            );
        }

        if (!$response->isSuccess()) {
            Yii::error('Walletto currencyExchangeRates err: ' . $response->json());
            $errorMessage = $response->json();
            $currencyExchangeRatesResponse->status = BaseResponse::STATUS_ERROR;
            $currencyExchangeRatesResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage);
            return $currencyExchangeRatesResponse;
        }

        $currencyExchangeRatesResponse->status = BaseResponse::STATUS_DONE;
        $currencyExchangeRatesResponse->exchangeRates = $response->json('exchange_rates');
        return $currencyExchangeRatesResponse;
    }

    private static function getFailureMessage(ClientResponse $clientResponse): string
    {
        $json = $clientResponse->json();

        $failureMessage = $json['failure_message'] ?? null;
        if ($failureMessage) {
            return $failureMessage;
        }

        $orders = $json['orders'] ?? null;
        if ($orders && is_array($orders)) {
            return self::getOrdersFailureMessages($orders);
        }

        Yii::warning('WallettoBankAdapter failure message not found: ' . Json::encode($json));

        return self::ERROR_STATUS_MSG;
    }

    private static function getOrdersFailureMessages(array $orders): string
    {
        $messages = [];
        foreach ($orders as $order) {
            $msg = $order['failure_message'] ?? null;
            if ($msg) {
                $messages[] = $msg;
            }
        }

        if (count($messages) === 0) {
            Yii::warning('WallettoBankAdapter orders failure messages not found: ' . Json::encode($orders));

            return self::ERROR_STATUS_MSG;
        }

        return join(PHP_EOL, $messages);
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        // TODO: Implement sendP2p() method.
    }
}
