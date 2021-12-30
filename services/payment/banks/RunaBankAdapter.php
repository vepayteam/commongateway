<?php


namespace app\services\payment\banks;


use app\services\ident\exceptions\RunaIdentException;
use app\services\ident\forms\RunaIdentInitForm;
use app\services\ident\forms\RunaIdentStateForm;
use app\services\ident\interfaces\RunaIdentResponseInteface;
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
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\Bank;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Vepay\Gateway\Client\Validator\ValidationException;
use Yii;
use yii\base\Model;

class RunaBankAdapter implements IBankAdapter
{
    const DOMAIN = 'https://ecommerce.runabank.ru/pc4x4';
    const DOMAIN_TEST = 'https://ecommerce-sec.runabank.ru/pc4x4';

    public static $bank = 11;
    private $domain;
    /** @var PartnerBankGate */
    private $gate;

    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        if(Yii::$app->params['TESTMODE'] == 'Y') {
            $this->domain = self::DOMAIN_TEST;
        } else {
            $this->domain = self::DOMAIN;
        }
        $this->gate = $partnerBankGate;
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
        // TODO: Implement confirm() method.
    }

    /**
     * @inheritDoc
     */
    public function createPay(CreatePayForm $createPayForm)
    {
        // TODO: Implement createPay() method.
    }

    /**
     * @inheritDoc
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        // TODO: Implement checkStatusPay() method.
    }

    /**
     * @inheritDoc
     */
    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        // TODO: Implement recurrentPay() method.
    }

    /**
     * @inheritDoc
     */
    public function refundPay(RefundPayForm $refundPayForm)
    {
        // TODO: Implement refundPay() method.
    }

    /**
     * @inheritDoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        // TODO: Implement outCardPay() method.
    }

    /**
     * @inheritDoc
     */
    public function getAftMinSum()
    {
        return Bank::findOne(self::$bank)->AftMinSum;
    }

    /**
     * @inheritDoc
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest)
    {
        // TODO: Implement getBalance() method.
    }

    /**
     * @inheritDoc
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        // TODO: Implement transferToAccount() method.
    }

    /**
     * @inheritDoc
     */
    public function identInit(Ident $ident)
    {
        $runaIdentInitForm = new RunaIdentInitForm();
        $runaIdentInitForm->cid_origin = $this->gate->Token;
        $runaIdentInitForm->passport_series = $ident->Series;
        $runaIdentInitForm->passport_number = $ident->Number;
        $runaIdentInitForm->name = $ident->FirstName;
        $runaIdentInitForm->surname = $ident->LastName;
        $runaIdentInitForm->patronymic = $ident->Patronymic;
        $runaIdentInitForm->inn = $ident->Inn;
        $runaIdentInitForm->snils = $ident->Snils;

//        if(!$runaIdentInitForm->validate()) {
//            throw new \Exception($runaIdentInitForm->getError());
//        }

        $ans = $this->sendIdentRequest('init', 'verify_docs', $runaIdentInitForm);
        $identInitResponse = new IdentInitResponse();
        $identInitResponse->response = $ans;
        if($ans['state_code'] != RunaIdentResponseInteface::RESPONSE_STATUS_INIT) {
            $identInitResponse->status = BaseResponse::STATUS_ERROR;
            $identInitResponse->message = $ans['state_description'] ?? 'Ошибка запроса';
        } else {
            $identInitResponse->status = BaseResponse::STATUS_DONE;
        }

        return $identInitResponse;
    }

    /**
     * @inheritDoc
     */
    public function identGetStatus(Ident $ident)
    {
        $initResponse = json_decode($ident->Response, true);

        if(!isset($initResponse['tid']) || empty($initResponse['tid'])) {
            throw new \Exception('Ошибка параметра запроса');
        }

        $runaIdentStateForm = new RunaIdentStateForm();
        $runaIdentStateForm->tid = $initResponse['tid'];
        $runaIdentStateForm->attach_smev_response = true;

        $identGetStatusResponse = new IdentGetStatusResponse();
        try {
            $ans = $this->sendIdentRequest('get_state', 'verify_docs', $runaIdentStateForm);
        } catch (RunaIdentException $e) {
            $identGetStatusResponse->status = BaseResponse::STATUS_ERROR;
            $identGetStatusResponse->message = $e->getMessage();
            $identGetStatusResponse->response = $ans ?? [];
            return $identGetStatusResponse;
        }

        $identGetStatusResponse->status = BaseResponse::STATUS_DONE;
        $identGetStatusResponse->identStatus = $this->convertIdentStateStatus($ans);
        $identGetStatusResponse->response = $ans;
        return $identGetStatusResponse;
    }

    /**
     * @param $method
     * @param $mode
     * @param Model $model
     * @return mixed
     * @throws \Exception
     */
    protected function sendIdentRequest($method, $mode, Model $model)
    {
        $certPath = Yii::getAlias('@app/config/runacert');
        $url = sprintf(
            '%s/%s/%s/%s',
            $this->domain,
            $mode,
            $this->gate->Login,
            $method
        );

        $data = $model->getAttributes();

        $curl = curl_init($url);

        Yii::warning('RunaBank req: ' . $url . ' ' . json_encode($data));
        curl_setopt_array($curl, array(
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSLCERT => $certPath . '/vepay.crt',
            CURLOPT_SSLKEY => $certPath . '/vepay.key',
            CURLOPT_CERTINFO => true,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,

            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($data)
        ));

        try {
            $response = curl_exec($curl);
            $error = curl_error($curl);

            if(!empty($error)) {
                throw new RunaIdentException($error);
            }
            curl_close($curl);
            Yii::warning('RunaBank res: ' . $response);
            return json_decode($response, true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $response
     * @return int
     */
    protected function convertIdentStateStatus(array $response)
    {
        if(!isset($response['state_code'])) {
            return Ident::STATUS_ERROR;
        }

        switch($response['state_code']) {
            case '00000':
                return Ident::STATUS_SUCCESS;
            case '00008':
                return Ident::STATUS_WAITING;
            default:
                return Ident::STATUS_DENIED;
        }
    }

    public function currencyExchangeRates()
    {
        // TODO: Implement currencyExchangeRates() method.
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        // TODO: Implement sendP2p() method.
    }
}
