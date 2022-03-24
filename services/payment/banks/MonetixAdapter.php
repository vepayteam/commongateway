<?php

namespace app\services\payment\banks;

use app\Api\Client\AbstractClient;
use app\Api\Client\Client;
use app\models\extservice\HttpProxy;
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
use app\services\payment\forms\monetix\CreatePayRequest;
use app\services\payment\forms\monetix\models\CardModel;
use app\services\payment\forms\monetix\models\CustomerModel;
use app\services\payment\forms\monetix\models\GeneralModel;
use app\services\payment\forms\monetix\models\PaymentModel;
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
        // TODO: Implement confirm() method.
    }

    public function createPay(CreatePayForm $createPayForm)
    {

        $generalModel = new GeneralModel(
            (int)$this->gate->Login,
            (string)$createPayForm->getPaySchet()->ID
        );
        $cardModel = new CardModel();
        $cardModel->setScenario(CardModel::SCENARIO_IN);
        $cardModel->pan = (string)$createPayForm->CardNumber;
        $cardModel->year = 2000 + (int)$createPayForm->CardYear;
        $cardModel->month = (int)$createPayForm->CardMonth;
        $cardModel->card_holder = $createPayForm->CardHolder;
        $cardModel->cvv = $createPayForm->CardCVC;

        $customerModel = new CustomerModel((string)$createPayForm->getPaySchet()->ID, Yii::$app->request->remoteIP);
        $paymentModel = new PaymentModel(
            $createPayForm->getPaySchet()->getSummFull(),
            'Оплата по счету № ' . $createPayForm->getPaySchet()->ID,
            $createPayForm->getPaySchet()->currency->Code
        );
        $createPayRequest = new CreatePayRequest(
            $generalModel,
            $cardModel,
            $customerModel,
            $paymentModel
        );
        $generalModel->signature = $createPayRequest->buildSignature($this->gate->Token);

        if(!$createPayRequest->validate()) {
            throw new \Exception($createPayRequest->GetError());
        }
        $url = $this->bankUrl . '/v2/payment/card/sale';
        $bodyJson = Json::encode($createPayRequest);
        try {
            $response = $this->apiClient->request(
                AbstractClient::METHOD_POST,
                $url,
                $createPayRequest->jsonSerialize()
            );
            $a = 0;
        } catch (GuzzleException $e) {
            $a = 0;
        }

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
        $generalModel = new GeneralModel(
            0,
            (string)$outCardPayForm->getPaySchet()->ID
        );
        $cardModel = new CardModel();
        $cardModel->setScenario(CardModel::SCENARIO_OUT);
        $cardModel->setPan($outCardPayForm->getCardOut()->CardNumber);

        $customerModel = new CustomerModel(Yii::$app->request->remoteIP);

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