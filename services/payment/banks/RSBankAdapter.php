<?php


namespace app\services\payment\banks;


use app\models\payonline\Uslugatovar;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\RefundPayException;
use app\services\payment\exceptions\RSbankAdapterExeception;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CheckStatusPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\rsb_aft\CreatePayRequest;
use app\services\payment\forms\rsb_aft\CheckStatusPayRequest;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use Yii;
use yii\helpers\Json;

class RSBankAdapter implements IBankAdapter
{
    public static $bank = 7;

    /** @var PartnerBankGate */
    protected $gate;

    protected $bankUrl;
    protected $bankUrl3DS;

    const BANK_URL = 'https://testsecurepay.rsb.ru:9443';
    const BANK_URL_TEST = 'https://testsecurepay.rsb.ru:9443';

    const BANK_URL_3DS = 'https://testsecurepay.rsb.ru/ecomm2/ClientHandler';
    const BANK_URL_3DS_TEST = 'https://testsecurepay.rsb.ru/ecomm2/ClientHandler';



    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;

        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y') {
            $this->bankUrl = self::BANK_URL_TEST;
            $this->bankUrl3DS = self::BANK_URL_3DS_TEST;
        } else {
            $this->bankUrl = self::BANK_URL;
            $this->bankUrl3DS = self::BANK_URL_3DS;
        }
    }

    /**
     * @inheritDoc
     */
    public function getBankId()
    {
        return self::$bank;
    }

    /**
     * @inheritDoc
     */
    public function confirm(DonePayForm $donePayForm)
    {
        $confirmPayResponse = new ConfirmPayResponse();
        $confirmPayResponse->status = BaseResponse::STATUS_DONE;
        return $confirmPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function confirmPay($idpay, $org = 0, $isCron = false)
    {
        // TODO: Implement confirmPay() method.
    }

    /**
     * @inheritDoc
     */
    public function transferToCard(array $data)
    {
        // TODO: Implement transferToCard() method.
    }

    /**
     * @inheritDoc
     */
    public function createPay(CreatePayForm $createPayForm)
    {
        $uri = '/ecomm2/MerchantHandler';

        $paySchet = $createPayForm->getPaySchet();
        $createPayRequest = new CreatePayRequest();
        $createPayRequest->mrch_transaction_id = $paySchet->ID;
        $createPayRequest->amount = $paySchet->getSummFull();
        $createPayRequest->client_ip_addr = Yii::$app->request->remoteIP;
        $createPayRequest->cardname = $createPayForm->CardHolder;
        $createPayRequest->pan = $createPayForm->CardNumber;
        $createPayRequest->expiry = $createPayForm->CardYear . $createPayForm->CardMonth;
        $createPayRequest->cvc2 = $createPayForm->CardCVC;

        $createPayResponse = new CreatePayResponse();
        try {
            $data = $createPayRequest->getAttributes();

            // если операция через ecomm, код сценария не передаем
            if($this->gate->TU == UslugatovarType::POGASHECOM) {
                unset($data['ecomm_payment_scenario']);
            }

            $ans = $this->sendRequest($uri, $data);
            if(array_key_exists('error', $ans)) {
                $createPayResponse->status = BaseResponse::STATUS_ERROR;
                $createPayResponse->message = $ans['error'];
            } else {
                $createPayResponse->isNeed3DSRedirect = false;
                $createPayResponse->status = BaseResponse::STATUS_DONE;
                $createPayResponse->transac = $ans['TRANSACTION_ID'];
                $createPayResponse->url = $this->bankUrl3DS . '?trans_id=' . urlencode($ans['TRANSACTION_ID']);
            }
        } catch (RSbankAdapterExeception $e) {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = 'Ошибка запроса';
        }

        return $createPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function PayXml(array $params)
    {
        // TODO: Implement PayXml() method.
    }

    /**
     * @inheritDoc
     */
    public function PayApple(array $params)
    {
        // TODO: Implement PayApple() method.
    }

    /**
     * @inheritDoc
     */
    public function PayGoogle(array $params)
    {
        // TODO: Implement PayGoogle() method.
    }

    /**
     * @inheritDoc
     */
    public function PaySamsung(array $params)
    {
        // TODO: Implement PaySamsung() method.
    }

    /**
     * @inheritDoc
     */
    public function ConfirmXml(array $params)
    {
        // TODO: Implement ConfirmXml() method.
    }

    /**
     * @inheritDoc
     */
    public function reversOrder($IdPay)
    {
        // TODO: Implement reversOrder() method.
    }

    /**
     * @inheritDoc
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        $uri = '/ecomm2/MerchantHandler';

        $paySchet = $okPayForm->getPaySchet();
        $checkStatusPayRequest = new CheckStatusPayRequest();
        $checkStatusPayRequest->trans_id = $paySchet->ExtBillNumber;
        $checkStatusPayRequest->client_ip_addr = Yii::$app->request->remoteIP;

        $ans = $this->sendRequest($uri, $checkStatusPayRequest->getAttributes());

        $checkStatusPayResponse = new CheckStatusPayResponse();
        switch ($ans['RESULT']) {
            case 'CREATED':
            case 'PENDING':
                $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
                break;
            case 'OK':
                $checkStatusPayResponse->status = BaseResponse::STATUS_DONE;
                break;
            case 'REVERSED':
            case 'AUTOREVERSED':
                $checkStatusPayResponse->status = BaseResponse::STATUS_CANCEL;
                break;
            default:
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                break;
        }
        $checkStatusPayResponse->rrn = $ans['RRN'];
        return $checkStatusPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        $uri = '/ecomm2/MerchantHandler';

    }

    /**
     * @inheritDoc
     */
    public function refundPay(RefundPayForm $refundPayForm)
    {
        // TODO: Implement refundPay() method.
    }

    /**
     * @param string $uri
     * @param array $data
     * @return array|string
     * @throws RSbankAdapterExeception
     */
    protected function sendRequest(string $uri, array $data)
    {
        $curl = curl_init();

        $url = $this->bankUrl . $uri;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_USERAGENT => Yii::$app->request->userAgent,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSLCERT => Yii::getAlias('@app/config/rsb/' . $this->gate->Login . '.pem'),
            CURLOPT_SSLKEY => Yii::getAlias('@app/config/rsb/' . $this->gate->Login . '.key'),
            CURLOPT_CAINFO => Yii::getAlias('@app/config/rsb/chain-ecomm-ca-root-ca.crt'),
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
        ));

        Yii::warning('RsBanAdapter req uri=' . $uri .' : ' . Json::encode($data));
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if(empty($curlError) && $info['http_code'] == 200) {
            return $this->parseResponse($response);
        } else {
            throw new RSbankAdapterExeception('Ошибка запроса: ' . $curlError);
        }
    }

    /**
     * @param string $response
     * @return array
     */
    protected function parseResponse(string $response)
    {
        $return = [];

        foreach (explode("\n", $response) as $row) {

            $rowData = explode(': ', $row);
            $return[$rowData[0]] = $rowData[1];
        }
        return $return;
    }
}
