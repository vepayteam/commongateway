<?php

namespace app\services\payment\banks;

use app\Api\Client\AbstractClient;
use app\Api\Client\Client;
use app\models\extservice\HttpProxy;
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
use app\services\payment\forms\monetix\CheckStatusPayRequest;
use app\services\payment\forms\monetix\CreatePayRequest;
use app\services\payment\forms\monetix\DonePayRequest;
use app\services\payment\forms\monetix\models\AcsReturnUrlModel;
use app\services\payment\forms\monetix\models\CardModel;
use app\services\payment\forms\monetix\models\CustomerModel;
use app\services\payment\forms\monetix\models\GeneralModel;
use app\services\payment\forms\monetix\models\PaymentModel;
use app\services\payment\forms\monetix\models\ReturnUrlModel;
use app\services\payment\forms\monetix\OutCardPayRequest;
use app\services\payment\forms\monetix\RefundPayRequest;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\helpers\DectaHelper;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Vepay\Gateway\Client\Validator\ValidationException;
use Yii;
use yii\helpers\Json;

class MonetixAdapter implements IBankAdapter
{
    use HttpProxy;

    const BANK_URL = 'https://api.trxhost.com';

    public static $bank = 14;

    /** @var PartnerBankGate $gate */
    protected $gate;
    /** @var Client $apiClient */
    protected $apiClient;
    protected $bankUrl;

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        $this->bankUrl = self::BANK_URL;
        $infoMessage = sprintf(
            'partnerId=%d bankId=%d',
            $this->gate->PartnerId,
            $this->getBankId()
        );
        if(Yii::$app->params['TESTMODE'] === 'Y') {
            $this->apiClient = new Client([
                RequestOptions::PROXY => str_replace('@', '%40', $this->proxyUser) . '@' . $this->proxyHost,
            ], $infoMessage);
        } else {
            $this->apiClient = new Client([], $infoMessage);
        }

    }

    public function getBankId()
    {
        return self::$bank;
    }

    public function confirm(DonePayForm $donePayForm)
    {
        $donePayRequest = new DonePayRequest();
        $generalModel = new GeneralModel($this->gate->Login, $donePayForm->getPaySchet()->ID);
        $donePayRequest->general = $generalModel;
        $donePayRequest->pares = $donePayForm->paRes;
        $generalModel->signature = $donePayRequest->buildSignature($this->gate->Token);

        $confirmPayResponse = new ConfirmPayResponse();
        $url = $this->bankUrl . '/v2/payment/card/3ds_result';
        try {
            $response = $this->apiClient->request(
                AbstractClient::METHOD_POST,
                $url,
                $donePayRequest->jsonSerialize()
            )->json();
            Yii::warning('Monetix confirm response: ' . Json::encode($response));

            $confirmPayResponse->status = BaseResponse::STATUS_DONE;
            return $confirmPayResponse;
        } catch (\Exception $e) {
            $confirmPayResponse->status = BaseResponse::STATUS_ERROR;
            $confirmPayResponse->message = $e->getMessage();
        }
    }

    public function createPay(CreatePayForm $createPayForm)
    {
        $callbackUrl = Yii::$app->params['domain'] . '/callback/monetix';
        $generalModel = new GeneralModel(
            (int)$this->gate->Login,
            (string)$createPayForm->getPaySchet()->ID
        );
        $generalModel->terminal_callback_url = $callbackUrl;

        $cardModel = new CardModel();
        $cardModel->setScenario(CardModel::SCENARIO_IN);
        $cardModel->pan = (string)$createPayForm->CardNumber;
        $cardModel->year = 2000 + (int)$createPayForm->CardYear;
        $cardModel->month = (int)$createPayForm->CardMonth;
        $cardModel->card_holder = $createPayForm->CardHolder;
        $cardModel->cvv = $createPayForm->CardCVC;

        $createPayRequest = new CreatePayRequest();
        $createPayRequest->general = $generalModel;
        $createPayRequest->card = $cardModel;
        $createPayRequest->customer = new CustomerModel(
            (string)$createPayForm->getPaySchet()->ID,
            Yii::$app->request->remoteIP
        );
        $createPayRequest->payment = new PaymentModel(
            $createPayForm->getPaySchet()->getSummFull(),
            'Pay ' . $createPayForm->getPaySchet()->ID,
            $createPayForm->getPaySchet()->currency->Code
        );
        $acsReturnUrlModel = new AcsReturnUrlModel();
        $acsReturnUrlModel->return_url = $createPayForm->getPaySchet()->getOrderdoneUrl();
        $acsReturnUrlModel->notification_url_3ds = $callbackUrl;

        $createPayRequest->return_url = new ReturnUrlModel($createPayForm->getPaySchet()->getOrderdoneUrl());
        $createPayRequest->acs_return_url = $acsReturnUrlModel;
        $generalModel->signature = $createPayRequest->buildSignature($this->gate->Token);

        $createPayResponse = new CreatePayResponse();
        $createPayResponse->isNeedPingMonetix = true;
        if(!$createPayRequest->validate()) {
            throw new \Exception($createPayRequest->GetError());
        }
        $url = $this->bankUrl . '/v2/payment/card/sale';
        try {
            $response = $this->apiClient->request(
                AbstractClient::METHOD_POST,
                $url,
                $createPayRequest->jsonSerialize()
            )->json();
            Yii::warning('Monetix createpay response: ' . Json::encode($response));

            if(isset($response['status']) && $response['status'] == 'success') {
                $createPayResponse->status = BaseResponse::STATUS_DONE;
                $createPayResponse->message = $response['status'];
                $createPayResponse->transac = $response['request_id'];
            } else {
                $createPayResponse->status = BaseResponse::STATUS_ERROR;
                $createPayResponse->message = $response['status'] ?? '';
            }
            return $createPayResponse;
        } catch (\Exception $e) {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = $e->getMessage();
        }
    }

    public function checkStatusPay(OkPayForm $okPayForm)
    {
        $checkStatusPayRequest = new CheckStatusPayRequest();
        $checkStatusPayRequest->general = [
            'project_id' => intval($this->gate->Login),
            'payment_id' => strval($okPayForm->getPaySchet()->ID),
        ];
        $checkStatusPayRequest->general['signature'] = $checkStatusPayRequest->buildSignature($this->gate->Token);
        $url = $this->bankUrl . '/v2/payment/status';

        $checkStatusPayResponse = new CheckStatusPayResponse();
        try {
            $response = $this->apiClient->request(
                AbstractClient::METHOD_POST,
                $url,
                $checkStatusPayRequest->jsonSerialize()
            )->json();

            $operation = $response['operations'][count($response['operations']) - 1];
            $checkStatusPayResponse->status = $this->converStatus($operation['status']);
            $checkStatusPayResponse->message = $operation['code'] . ': ' . $operation['status'];
            return $checkStatusPayResponse;
        } catch (\Exception $e) {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = $e->getMessage();
            return $checkStatusPayResponse;
        }
    }

    private function converStatus($status)
    {
        $createdStatuses = [
            'awaiting 3ds result',
            'awaiting redirect result',
            'awaiting clarification',
            'awaiting customer action',
            'awaiting merchant auth',
            'processing',
        ];
        if($status == 'success') {
            return BaseResponse::STATUS_DONE;
        } elseif (in_array($status, $createdStatuses)) {
            return BaseResponse::STATUS_CREATED;
        } else {
            return BaseResponse::STATUS_ERROR;
        }
    }

    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        // TODO: Implement recurrentPay() method.
    }

    public function refundPay(RefundPayForm $refundPayForm)
    {
        $refundPayRequest = new RefundPayRequest();
        $generalModel = new GeneralModel(
            intval($this->gate->Login),
            strval($refundPayForm->paySchet->ID)
        );
        $refundPayRequest->general = $generalModel;
        $refundPayRequest->amount = $refundPayForm->paySchet->getSummFull();
        $refundPayRequest->currency = $refundPayForm->paySchet->currency->Code;
        $generalModel->signature = $refundPayRequest->buildSignature($this->gate->Token);

        $url = $this->bankUrl . '/v2/payment/card/refund';
        $refundPayResponse = new RefundPayResponse();
        try {
            $response = $this->apiClient->request(
                AbstractClient::METHOD_POST,
                $url,
                $refundPayRequest->jsonSerialize()
            )->json();

            $refundPayResponse->status = $this->converStatus($response['status']);
            $refundPayResponse->message = $response['status'];
            return $refundPayResponse;
        } catch (\Exception $e) {
            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
            $refundPayResponse->message = $e->getMessage();
            return $refundPayResponse;
        }
    }

    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        $generalModel = new GeneralModel(
            intval($this->gate->Login),
            strval($outCardPayForm->paySchet->ID)
        );
        $cardModel = new CardModel();
        $cardModel->setScenario(CardModel::SCENARIO_OUT);
        $cardModel->pan = strval($outCardPayForm->cardnum);

        $customerModel = new CustomerModel(
            (string)$outCardPayForm->paySchet->ID,
            Yii::$app->request->remoteIP
        );
        $customerModel->first_name = $outCardPayForm->getFirstName();
        $customerModel->last_name = $outCardPayForm->getLastName();
        $customerModel->middle_name = $outCardPayForm->getMiddleName();

        $paymentModel = new PaymentModel(
            $outCardPayForm->getAmount(),
            'PayOut ' . $outCardPayForm->paySchet->ID,
            $outCardPayForm->paySchet->currency->Code
        );

        $outCardPayRequest = new OutCardPayRequest();
        $outCardPayRequest->general = $generalModel;
        $outCardPayRequest->card = $cardModel;
        $outCardPayRequest->customer = $customerModel;
        $outCardPayRequest->payment = $paymentModel;
        $generalModel->signature = $outCardPayRequest->buildSignature($this->gate->Token);

        $url = $this->bankUrl . '/v2/payment/card/payout';
        $outCardPayResponse = new OutCardPayResponse();
        try {
            $response = $this->apiClient->request(
                AbstractClient::METHOD_POST,
                $url,
                $outCardPayRequest->jsonSerialize()
            )->json();

            if(isset($response['status']) && $response['status'] == 'success') {
                $outCardPayResponse->status = BaseResponse::STATUS_DONE;
                $outCardPayResponse->message = $response['status'];
                $outCardPayResponse->trans = $response['request_id'];
            } else {
                $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
                $outCardPayResponse->message = $response['status'] ?? '';
            }
            return $outCardPayResponse;
        } catch (\Exception $e) {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $e->getMessage();
            return $outCardPayResponse;
        }
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

    public function registrationBenific(RegistrationBenificForm $registrationBenificForm)
    {
        // TODO: Implement registrationBenific() method.
    }
}