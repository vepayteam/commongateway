<?php

namespace app\services\payment\banks;

use app\Api\Client\Client;
use app\services\ident\forms\IdentForm;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\traits\WalletoRequestTrait;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\MerchantRequestAlreadyExistsException;
use app\services\payment\exceptions\RefundPayException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CheckStatusPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\GetBalanceForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Yii;

class WalletoBankAdapter implements IBankAdapter
{
    use WalletoRequestTrait;

    public static $bank = 10;
    private const BANK_URL = 'https://api.sandbox.walletto.eu';
    private const KEY_ROOT_PATH = '@app/config/walleto/';
    /** @var PartnerBankGate */
    protected $gate;
    /** @var Client $api */
    protected $api;
    /** @var String $bankUrl */
    protected $bankUrl;

    // Walleto bank statuses
    private const STATUS_PREPARED = 'prepared';
    private const STATUS_SUCCESS = 'success';
    private const STATUS_CHARGED = 'charged';
    private const STATUS_REFUNDED = 'refunded';
    private const STATUS_AUTHORIZED = 'authorized';
    private const STATUS_REVERSED = 'reversed';


    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        $this->bankUrl = self::BANK_URL;
        $apiClientHeader = [
            'Authorization' => $partnerBankGate->Token,
        ];
        //TODO: move certificates/keys from git directories
        $config = [
            RequestOptions::VERIFY => Yii::getAlias(self::KEY_ROOT_PATH . $partnerBankGate->Login . '.pem'),
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
        $request = $this->formatCreatePayRequest($createPayForm); // request
        $createPayResponse = new CreatePayResponse();
        try {
            $response = $this->api->request(
                Client::METHOD_POST,
                $url,
                $request->getAttributes()
            );
        } catch (GuzzleException $e) {
            Yii::error('Walleto payInCreate err: ' . $e->getMessage());
            throw new CreatePayException(BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' .  $e->getMessage());
        }
        if (!$response->isSuccess()) {
            Yii::error('Walleto payInCreate err: ' . $response->json('failure_message'));
            $errorMessage = $response->json('failure_message');
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = BankAdapterResponseException::setErrorMsg($errorMessage ?? '');
            return $createPayResponse;
        }
        $responseData = $response->json('orders')[0];
        $createPayResponse->status = $this->convertStatus($responseData['status']);
        $createPayResponse->isNeed3DSRedirect = false;
        $createPayResponse->isNeed3DSVerif = true;
        $createPayResponse->transac = $responseData['id'];
        $createPayResponse->url = $responseData['form3d']['action']; //Acquirer ACS URL
        $createPayResponse->md = $responseData['form3d']['MD'];
        $createPayResponse->pa = $responseData['form3d']['PaReq'];
        return $createPayResponse;
    }

    public function checkStatusPay(OkPayForm $okPayForm)
    {
        //TODO: !!!
    }

    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        // TODO: Implement recurrentPay() method.
    }

    public function refundPay(RefundPayForm $refundPayForm)
    {
        // TODO: Implement refundPay() method.
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

    public function ident(IdentForm $identForm)
    {
        // TODO: Implement ident() method.
    }

    /**
     * @param string $status
     * @return int
     */
    public function convertStatus(string $status): int
    {
        switch ($status) {
            case self::STATUS_PREPARED:
            case self::STATUS_CHARGED:
                return BaseResponse::STATUS_CREATED;
            case self::STATUS_SUCCESS:
                return BaseResponse::STATUS_DONE;
            case self::STATUS_REFUNDED:
                return BaseResponse::STATUS_CANCEL;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }
}
