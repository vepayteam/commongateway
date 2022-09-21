<?php


namespace app\services\payment\banks;


use app\helpers\Modifiers;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\createPayResponse\AcsRedirectData;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
use app\services\payment\banks\bank_adapter_responses\IdentInitResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\bank_adapter_responses\RegistrationBenificResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\data\ClientData;
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
use app\services\payment\forms\gratapay\CreatePayRequest;
use app\services\payment\forms\gratapay\OutCardPayRequest;
use app\services\payment\forms\gratapay\RefundPayRequest;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\models\Bank;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\types\AccountTypes;
use GuzzleHttp\Exception\BadResponseException;
use Vepay\Gateway\Client\Validator\ValidationException;
use Yii;
use yii\helpers\Json;

class GratapayAdapter extends BaseAdapter implements IBankAdapter
{
    private const PROD_IN_PAYMENT_SYSTEM = 'CardGate';
    private const TEST_IN_S2S_PAYMENT_SYSTEM = 'CardGateTestS2S';
    private const PROD_OUT_PAYMENT_SYSTEM = 'Card';
    private const TEST_OUT_S2S_PAYMENT_SYSTEM = 'TestCard';

    private const DEFAULT_PHONE = '79009000000';

    const BANK_URL = 'https://psp.kiparisdmcc.ae/api';
    const AFT_MIN_SUMM = 185000;

    /**
     * @deprecated Use {@see bankId()} instead.
     */
    public static $bank = 13;

    protected $bankUrl;

    /** @var PartnerBankGate */
    protected $gate;
    /** @var \GuzzleHttp\Client */
    protected $apiClient;

    /**
     * {@inheritDoc}
     */
    public static function bankId(): int
    {
        return 13;
    }

    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->bankUrl = self::BANK_URL;
        $this->gate = $partnerBankGate;
        $this->apiClient = new \GuzzleHttp\Client();;
    }

    /**
     * @inheritDoc
     */
    public function getBankId()
    {
        return self::bankId();
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
    public function createPay(CreatePayForm $createPayForm, ClientData $clientData)
    {
        $paySchet = $createPayForm->getPaySchet();
        $createPayRequest = new CreatePayRequest();
        $createPayRequest->transaction_id = $paySchet->ID;
        $createPayRequest->amount = $paySchet->getSummFull() / 100;
        $createPayRequest->currency = $paySchet->currency->Code;
        $createPayRequest->url = $createPayRequest->getUrls($paySchet);
        $createPayRequest->system_fields = $createPayRequest->getSystemFields($createPayForm);
        $createPayRequest->three_ds_v2 = $createPayRequest->getThreeDsV2();

        if(Yii::$app->params['TESTMODE'] == 'Y') {
            $createPayRequest->payment_system = self::TEST_IN_S2S_PAYMENT_SYSTEM;
        } else {
            $createPayRequest->payment_system = self::PROD_IN_PAYMENT_SYSTEM;
        }

        $createPayResponse = new CreatePayResponse();
        $bodyJson = Json::encode($createPayRequest->getAttributes());
        try {
            $url = $this->bankUrl . '/deposit/create';
            Yii::warning('GratepayAdapter req uri=' . $url . ' data=' . $bodyJson);
            $response = $this->apiClient->post($url, [
                'headers' => $this->buildRequestHeaders($bodyJson),
                'body' => $bodyJson,
            ]);
            Yii::warning('GratepayAdapter ans uri=' . $url . ' data=' . $response->getBody());
            $responseBody = Json::decode($response->getBody(), true);
            if($responseBody['status'] == 'created') {
                $createPayResponse->status = BaseResponse::STATUS_DONE;
                $createPayResponse->transac = $responseBody['id'];
                $createPayResponse->isNeed3DSRedirect = true;

                if ($responseBody['redirect']['method'] === 'GET') {
                    $createPayResponse->url = $responseBody['redirect']['url'] . '?'
                        . http_build_query($responseBody['redirect']['params']);
                    $createPayResponse->acs = new AcsRedirectData(
                        AcsRedirectData::STATUS_OK,
                        $createPayResponse->url,
                        'GET'
                    );
                } elseif ($responseBody['redirect']['method'] === 'POST') {
                    $createPayResponse->url = $responseBody['redirect']['url'];
                    $createPayResponse->acs = new AcsRedirectData(
                        AcsRedirectData::STATUS_OK,
                        $createPayResponse->url,
                        'POST',
                        $responseBody['redirect']['params']
                    );
                }
            } else {
                $createPayResponse->status = BaseResponse::STATUS_ERROR;
                $createPayResponse->message = substr($responseBody['message'] ?? '', 0, 255);
            }
        } catch (\Exception $e) {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = substr($e->getMessage(), 0, 255);
        }

        return $createPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        $checkStatusPayResponse = new CheckStatusPayResponse();

        try {
            $url = $this->bankUrl . '/status/' . $okPayForm->getPaySchet()->ExtBillNumber;
            Yii::warning('GratepayAdapter req uri=' . $url);
            $response = $this->apiClient->get($url, [
                'headers' => $this->buildRequestHeaders(''),
            ]);
            Yii::warning('GratepayAdapter ans uri=' . $url . ' data=' . $response->getBody());
            $responseBody = Json::decode($response->getBody(), true);
            $checkStatusPayResponse->status = $this->convertCheckStatusPayResult($responseBody['status']);
            $checkStatusPayResponse->cardNumber = $responseBody['system_fields']['card_number'] ?? $responseBody['status'];
        } catch (\Exception $e) {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = substr($e->getMessage(), 0, 255);
        }
        return $checkStatusPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function refundPay(RefundPayForm $refundPayForm)
    {
        $paySchet = $refundPayForm->paySchet;
        $sourcePaySchet = $paySchet->refundSource;

        $refundPayRequest = new RefundPayRequest();
        $refundPayRequest->amount = round($paySchet->getSummFull() / 100, 2);
        $refundPayRequest->currency = $paySchet->currency->Code;
        $refundPayRequest->original_transaction_id = $sourcePaySchet->ExtBillNumber;
        $refundPayRequest->transaction_id = $paySchet->ID;

        $bodyJson = Json::encode($refundPayRequest->getAttributes());
        $refundPayResponse = new RefundPayResponse();
        try {
            $url = $this->bankUrl . '/refund/create';
            Yii::warning('GratepayAdapter req uri=' . $url . ' data=' . $bodyJson);
            $response = $this->apiClient->post($url, [
                'headers' => $this->buildRequestHeaders($bodyJson),
                'body' => $bodyJson,
            ]);
            Yii::warning('GratepayAdapter ans uri=' . $url . ' data=' . $response->getBody());
            $responseBody = Json::decode($response->getBody(), true);
            $status = $this->convertRefundPayResult($responseBody['status']);
            $refundPayResponse->status = $status;
            $refundPayResponse->message = $responseBody['message'] ?? $responseBody['status'];
        } catch (\Exception $e) {
            if ($e instanceof BadResponseException) {
                $response = $e->getResponse();
                Yii::warning('GratepayAdapter response error uri=' . $url . ' data=' . $response->getBody());
            }
            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
            $refundPayResponse->message = substr($e->getMessage(), 0, 255);
        }
        return $refundPayResponse;
    }

    /**
     * @inheritDoc
     * @todo Improve error handling.
     */
    public function outCardPay(OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        $paySchet = $outCardPayForm->paySchet;

        $request = new OutCardPayRequest();
        $request->transaction_id = $paySchet->ID;
        $request->amount = number_format($paySchet->getSummFull() / 100, 2, '.', '');
        $request->currency = $paySchet->currency->Code;
        if(Yii::$app->params['TESTMODE'] == 'Y') {
            $request->payment_system = self::TEST_OUT_S2S_PAYMENT_SYSTEM;
        } else {
            $request->payment_system = self::PROD_OUT_PAYMENT_SYSTEM;
        }

        $phone = preg_replace('/\D/', '', $outCardPayForm->phone); // only numbers
        if (empty($phone)) {
            $phone = self::DEFAULT_PHONE;
        }
        $request->system_fields = [
            'card_number' => $outCardPayForm->cardnum,
            'client_phone' => '+' . $phone,
            'payment_description' => 'Payout ' . $paySchet->ID,
        ];

        $bodyJson = Json::encode($request->getAttributes());
        $outCardPayResponse = new OutCardPayResponse();

        $url = $this->bankUrl . '/deduce/create';
        Yii::warning('GratepayAdapter req uri=' . $url . ' data=' . Modifiers::searchAndReplacePan($bodyJson));
        try {
            $response = $this->apiClient->post($url, [
                'headers' => $this->buildRequestHeaders($bodyJson),
                'body' => $bodyJson,
            ]);
            Yii::warning('GratepayAdapter ans uri=' . $url . ' data=' . $response->getBody());
            $responseBody = Json::decode($response->getBody(), true);
            if($responseBody['status'] == 'created') {
                $outCardPayResponse->status = BaseResponse::STATUS_DONE;
                $outCardPayResponse->trans = $responseBody['id'];
            } else {
                $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
                $outCardPayResponse->message = substr($responseBody['message'] ?? '', 0, 255);
            }
        } catch (\Exception $e) {
            if ($e instanceof BadResponseException) {
                $response = $e->getResponse();
                Yii::warning("GratepayAdapter response error uri={$url} HTTP code={$response->getStatusCode()} data={$response->getBody()}");
            }
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = substr($e->getMessage(), 0, 255);
        }

        return $outCardPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function getAftMinSum()
    {
        return $this->getBankModel()->AftMinSum ?? self::AFT_MIN_SUMM;
    }

    /**
     * @inheritDoc
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest)
    {
        $getBalanceResponse = new GetBalanceResponse();
        try {
            $url = $this->bankUrl . '/balance';
            Yii::warning('GratepayAdapter req uri=' . $url);
            $response = $this->apiClient->get($url, [
                'headers' => $this->buildRequestHeaders(''),
            ]);
            Yii::warning('GratepayAdapter ans uri=' . $url . ' data=' . $response->getBody());
            $responseBody = Json::decode($response->getBody(), true);

            $summ = 0;
            foreach ($responseBody['balances'] as $balance) {
                if(array_key_exists($getBalanceRequest->currency, $balance)) {
                    $summ += $balance[$getBalanceRequest->currency];
                }
            }
            $getBalanceResponse->amount = $summ;
            $getBalanceResponse->account_type = AccountTypes::TYPE_TRANSIT_PAY_IN;
            $getBalanceResponse->currency = $getBalanceRequest->currency;
            $getBalanceResponse->bank_name = Bank::findOne(['ID' => $this->getBankId()])->Name;
        } catch (\Exception $e) {
            $getBalanceResponse->status = BaseResponse::STATUS_ERROR;
            $getBalanceResponse->message = substr($e->getMessage(), 0, 255);
        }
        return $getBalanceResponse;
    }

    /**
     * @inheritDoc
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function identInit(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function identGetStatus(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    public function currencyExchangeRates()
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @param string $status
     * @return int
     */
    private function convertCheckStatusPayResult(string $status)
    {
        switch($status) {
            case 'ok':
                return BaseResponse::STATUS_DONE;
            case 'pending':
                return BaseResponse::STATUS_CREATED;
            case 'cancel':
                return BaseResponse::STATUS_CANCEL;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @param string $status
     * @return int
     */
    private function convertRefundPayResult(string $status)
    {
        switch($status) {
            case 'ok':
                return BaseResponse::STATUS_DONE;
            case 'created':
                return BaseResponse::STATUS_CREATED;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @param string $status
     * @return int
     */
    private function convertGetBalanceResult(string $status)
    {
        switch($status) {
            case 'ok':
                return BaseResponse::STATUS_DONE;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @param string $bodyJson
     * @return array
     */
    private function buildRequestHeaders(string $bodyJson)
    {
        $sign = md5($bodyJson . $this->gate->Token);
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Auth' => $this->gate->Login,
            'Sign' => $sign,
        ];
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function registrationBenific(RegistrationBenificForm $registrationBenificForm)
    {
        throw new GateException('Метод недоступен');
    }
}
