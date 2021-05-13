<?php

namespace app\services\payment\banks;

use app\Api\Client\Client;
use app\services\ident\forms\IdentForm;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
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
use GuzzleHttp\RequestOptions;
use Vepay\Gateway\Client\NativeClient;
use Yii;

class WalletoBankAdapter implements IBankAdapter
{

    public static $bank = 10;

    private const BANK_URL = 'https://api.sandbox.walletto.eu';
    private const KEY_ROOT_PATH = '@app/config/walleto/';
    /** @var PartnerBankGate */
    protected $gate;
    protected $bankUrl;
    /**
     * @var NativeClient
     */
    protected $api;

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

    public function getBankId()
    {
        return self::$bank;
    }

    public function confirm(DonePayForm $donePayForm)
    {
        // TODO: Implement confirm() method.
    }

    public function createPay(CreatePayForm $createPayForm)
    {
        // TODO: Implement createPay() method.
    }

    public function checkStatusPay(OkPayForm $okPayForm)
    {
        // TODO: Implement checkStatusPay() method.
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

    public function getBalance(GetBalanceForm $getBalanceForm)
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
}
