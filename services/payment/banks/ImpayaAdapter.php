<?php

namespace app\services\payment\banks;

use app\models\payonline\Cards;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
use app\services\payment\banks\bank_adapter_responses\IdentInitResponse;
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
use app\services\payment\forms\impaya\CreatePayRequest;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Vepay\Gateway\Client\Validator\ValidationException;
use Yii;
use yii\helpers\Json;

class ImpayaAdapter implements IBankAdapter
{

    public static $bank = 15;
    protected $bankUrl;

    /** @var PartnerBankGate */
    protected $gate;

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        if(Yii::$app->params['TESTMODE'] === 'Y') {
            $this->bankUrl = 'https://www.impaya.online/new';
        } else {
            $this->bankUrl = 'https://www.impaya.online/new';
        }

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
        $paySchet = $createPayForm->getPaySchet();
        $createPayRequest = new CreatePayRequest();
        $createPayRequest->merchant_id = $this->gate->Login;
        $createPayRequest->amount = $paySchet->getSummFull() / 100;
        $createPayRequest->currency = $paySchet->currency->Code;
        $createPayRequest->invoice = $paySchet->ID;
        $createPayRequest->cl_ip = Yii::$app->request->remoteIP;
        $createPayRequest->description = 'Оплата счета №' . $paySchet->ID;
        $createPayRequest->cc_name = $createPayForm->CardHolder;
        $createPayRequest->cc_num = $createPayForm->CardNumber;
        $createPayRequest->cc_expire_m = $createPayForm->CardMonth;
        $createPayRequest->cc_expire_y = '20' . $createPayForm->CardYear;
        $createPayRequest->cc_cvc = $createPayForm->CardCVC;
        $createPayRequest->buildHash($this->gate->Token);
        $createPayRequest->cl_fname = $paySchet->ID;
        $createPayRequest->cl_email = $paySchet->ID . '@vepay.online';

        $uri = '/h2h/';
        $this->sendRequest($uri, $createPayRequest->getAttributes());
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

    public function getBalance(GetBalanceRequest $getBalanceRequest)
    {
        // TODO: Implement getBalance() method.
    }

    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        // TODO: Implement transferToAccount() method.
    }

    public function identInit(Ident $ident)
    {
        // TODO: Implement identInit() method.
    }

    public function identGetStatus(Ident $ident)
    {
        // TODO: Implement identGetStatus() method.
    }

    public function currencyExchangeRates()
    {
        // TODO: Implement currencyExchangeRates() method.
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        // TODO: Implement sendP2p() method.
    }

    private function sendRequest ($url, $post) {
        foreach ($post as $k => $v) {
            $post_l[] = $k."=".$v;
        }

        $ch = curl_init($this->bankUrl . $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $post_l));
        #curl_setopt($ch, CURLOPT_FOLLOWLOCATION  	, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $curl = curl_exec($ch);

        $ans = [];
        switch ($curl->responseCode) {
            case 200:

            case 202:
                $ans['xml'] = Json::decode($curl->response);
                break;
            case 500:
                $ans['error'] = $curl->responseCode;
                $ans['httperror'] = Json::decode($curl->response);
                break;
            default:
                $ans['error'] = $curl->errorCode;
                break;
        }


        return $ans;
    }
}