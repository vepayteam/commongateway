<?php


namespace app\services\payment\banks;


use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\adg\CreatePayRequest;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CheckStatusPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\helpers\ADGroupBankHelper;
use app\services\payment\models\adb\ClientCardModel;
use app\services\payment\models\adb\OrderDataModel;
use app\services\payment\models\adb\TxDetailsModel;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Yii;
use yii\base\Model;

class ADGroupBankAdapter implements IBankAdapter
{
    public static $bank = 5;
    protected $bankUrl = 'https://qpg.adgroup.finance';

    /** @var PartnerBankGate */
    protected $gate;

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
    }

    public function getBankId()
    {
        return self::$bank;
    }

    public function confirm(DonePayForm $donePayForm)
    {
        // TODO: Implement confirm() method.
    }

    public function confirmPay($idpay, $org = 0, $isCron = false)
    {
        // TODO: Implement confirmPay() method.
    }

    public function transferToCard(array $data)
    {
        // TODO: Implement transferToCard() method.
    }

    public function createPay(CreatePayForm $createPayForm)
    {
        throw new GateException('Метод недоступен');
        $action = '/FE/rest/tx/sync/purchase';

        $createPayRequest = new CreatePayRequest();
        $createPayRequest->mId = ADGroupBankHelper::hashDataInBase64($this->gate->Login);
        $createPayRequest->maId = ADGroupBankHelper::hashDataInBase64($this->gate->Password);
        $createPayRequest->userName = ADGroupBankHelper::encryptDataInBase64($this->gate->AdvParam_1, $this->gate->Token);
        $createPayRequest->password = ADGroupBankHelper::encryptDataInBase64($this->gate->AdvParam_2, $this->gate->Token);

        $createPayRequest->buildData($createPayForm);

        $signatureBase = $createPayRequest->getSignature();
        $createPayRequest->signature = ADGroupBankHelper::createSignature($signatureBase, $this->gate->Token);


        $this->sendRequest($action, $createPayRequest->getAttributes());


    }

    public function PayXml(array $params)
    {
        // TODO: Implement PayXml() method.
    }

    public function PayApple(array $params)
    {
        // TODO: Implement PayApple() method.
    }

    public function PayGoogle(array $params)
    {
        // TODO: Implement PayGoogle() method.
    }

    public function PaySamsung(array $params)
    {
        // TODO: Implement PaySamsung() method.
    }

    public function ConfirmXml(array $params)
    {
        // TODO: Implement ConfirmXml() method.
    }

    public function reversOrder($IdPay)
    {
        // TODO: Implement reversOrder() method.
    }

    public function checkStatusPay(OkPayForm $okPayForm)
    {
        // TODO: Implement checkStatusPay() method.
    }

    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        // TODO: Implement recurrentPay() method.
    }

    protected function sendRequest(string $action, array $data)
    {
        $curl = curl_init();

        $url = $this->bankUrl . $action;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $a = 0;
    }

    public function refundPay(RefundPayForm $refundPayForm)
    {
        // TODO: Implement refundOrder() method.
    }

    /**
     * @inheritDoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        throw new GateException('Метод недоступен');
    }
}
