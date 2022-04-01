<?php

namespace app\services\payment\banks;

use app\models\payonline\Cards;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
use app\services\payment\banks\bank_adapter_responses\IdentInitResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\bank_adapter_responses\RegistrationBenificResponse;
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
use app\services\payment\forms\impaya\CheckStatusPayRequest;
use app\services\payment\forms\impaya\CreatePayRequest;
use app\services\payment\forms\impaya\OutCardPayRequest;
use app\services\payment\forms\impaya\RefundPayRequest;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Vepay\Gateway\Client\Validator\ValidationException;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

class ImpayaAdapter implements IBankAdapter
{
    private const PHONE_DEFAULT_VALUE = '79009000000';

    public static $bank = 15;
    protected $bankUrl;

    /** @var PartnerBankGate */
    protected $gate;

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        $this->bankUrl = 'https://www.impaya.online/new';
    }

    public function getBankId()
    {
        return self::$bank;
    }

    public function confirm(DonePayForm $donePayForm)
    {
        $confirmPayResponse = new ConfirmPayResponse();
        $confirmPayResponse->status = BaseResponse::STATUS_DONE;
        return $confirmPayResponse;
    }

    public function createPay(CreatePayForm $createPayForm): CreatePayResponse
    {
        $paySchet = $createPayForm->getPaySchet();
        $createPayRequest = new CreatePayRequest();
        $createPayRequest->merchant_id = $this->gate->Login;
        $createPayRequest->amount = $paySchet->getSummFull();
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
        $createPayRequest->cl_email = $paySchet->UserEmail ?? $paySchet->ID . '@vepay.online';
        $createPayRequest->cl_phone = $paySchet->PhoneUser ?? self::PHONE_DEFAULT_VALUE;

        $uri = '/h2h/';
        $ans = $this->sendRequest($uri, $createPayRequest->getAttributes());

        $createPayResponse = new CreatePayResponse();
        if($ans['status'] != BaseResponse::STATUS_DONE) {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = $ans['message'] ?? '';
            return $createPayResponse;
        }

        if($ans['data']['status_id'] != 2) {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->transac = $ans['data']['transaction']['transaction_id'] ?? '';
            $createPayResponse->message = $ans['data']['status_descr'] ?? 'Ошибка запроса';
            return $createPayResponse;
        }

        $createPayResponse->status = BaseResponse::STATUS_DONE;
        $createPayResponse->url = $ans['data']['3ds']['url'];
        $createPayResponse->transac = $ans['data']['transaction']['transaction_id'] ?? '';
        if($ans['data']['3ds']['method'] == 'post') {
            $createPayResponse->params3DS = $ans['data']['3ds']['params'];
        }

        return $createPayResponse;
    }

    public function checkStatusPay(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $checkStatusPayRequest = new CheckStatusPayRequest();
        $checkStatusPayRequest->invoice = $okPayForm->getPaySchet()->ExtBillNumber;
        $checkStatusPayRequest->merchant_id = $this->gate->Login;
        $checkStatusPayRequest->buildHash($this->gate->Token);
        $uri = '/gateway/';
        $ans = $this->sendRequest($uri, $checkStatusPayRequest->getAttributes());
        $checkStatusPayResponse = new CheckStatusPayResponse();

        $checkStatusPayResponse->message = $ans['data']['payment_system_status'] ?? '';
        $checkStatusPayResponse->status = self::convertStatus($ans['data']['status_id']);
        return $checkStatusPayResponse;
    }

    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function refundPay(RefundPayForm $refundPayForm): RefundPayResponse
    {
        $refundPayRequest = new RefundPayRequest();
        $refundPayRequest->merchant_id = $this->gate->Login;
        $refundPayRequest->transaction_id = $refundPayForm->paySchet->ExtBillNumber;
        $refundPayRequest->amount = $refundPayForm->paySchet->getSummFull();
        $refundPayRequest->buildHash($this->gate->Token);
        $uri = '/api/';
        $ans = $this->sendRequest($uri, $refundPayRequest->getAttributes());

        $refundPayResponse = new RefundPayResponse();
        if($ans['data'] == "OK") {
            $refundPayResponse->status = BaseResponse::STATUS_DONE;
        } else {
            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
        }
        return $refundPayResponse;
    }

    public function outCardPay(OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        $outCardPayRequest = new OutCardPayRequest();
        $outCardPayRequest->merchant_id = $this->gate->Login;
        $outCardPayRequest->invoice = $outCardPayForm->paySchet->ID;
        $outCardPayRequest->amount = (int)$outCardPayForm->paySchet->getSummFull();
        $outCardPayRequest->currency = $outCardPayForm->paySchet->currency->Code;
        $outCardPayRequest->cc_num = $outCardPayForm->cardnum;
        $outCardPayRequest->phone = $outCardPayForm->phone ?? self::PHONE_DEFAULT_VALUE;
        $outCardPayRequest->fname = $outCardPayForm->getFirstName(true);
        $outCardPayRequest->lname = $outCardPayForm->getLastName(true);
        $outCardPayRequest->buildHash($this->gate->Token);
        $uri = '/api3/';
        $ans = $this->sendRequest($uri, $outCardPayRequest->getAttributes());

        $outCardPayResponse = new OutCardPayResponse;
        $outCardPayResponse->message = $ans['data']['status_descr'] ?? '';
        $baseStatus = self::convertStatus($ans['data']['status_id']);

        if($baseStatus == BaseResponse::STATUS_DONE || $baseStatus == BaseResponse::STATUS_CREATED) {
            $outCardPayResponse->status = BaseResponse::STATUS_DONE;
            $outCardPayResponse->trans = $ans['data']['transaction']['transaction_id'];
        } else {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
        }

        return $outCardPayResponse;
    }

    public function getAftMinSum()
    {
        // aft недоступен
        return PHP_INT_MAX;
    }

    public function getBalance(GetBalanceRequest $getBalanceRequest)
    {
        throw new GateException('Метод недоступен');
    }

    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function identInit(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    public function identGetStatus(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    public function currencyExchangeRates()
    {
        throw new GateException('Метод недоступен');
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        throw new GateException('Метод недоступен');
    }

    private function sendRequest ($url, $data): array
    {
        foreach ($data as $k => $v) {
            $post[] = $k."=".$v;
        }

        Yii::warning('Impaya req: ' . $url . ' ' . json_encode($data));
        $ch = curl_init($this->bankUrl . $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $post));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $ans = [];
        try {
            $curl = curl_exec($ch);
            Yii::warning('Impaya res: ' . $url . ' ' . $curl);
            $ans['status'] = BaseResponse::STATUS_DONE;
            try {
                $ans['data'] = Json::decode($curl);
            } catch (InvalidArgumentException $e) {
                $ans['data'] = $curl;
            }

        } catch (\Exception $e) {
            $ans['status'] = BaseResponse::STATUS_ERROR;
            $ans['message'] = $e->getMessage();
        }

        return $ans;
    }

    public static function convertStatus(int $status): int {
        switch ($status) {
            case 0:
            case 2:
            case 11:
                return BaseResponse::STATUS_CREATED;
            case 1:
            case 10:
                return BaseResponse::STATUS_DONE;
            case 5:
                return BaseResponse::STATUS_CANCEL;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    public function registrationBenific(RegistrationBenificForm $registrationBenificForm)
    {
        throw new GateException('Метод недоступен');
    }
}