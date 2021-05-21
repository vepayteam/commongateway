<?php


namespace app\services\payment\banks;


use app\models\TU;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\ident\forms\IdentForm;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
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
use app\services\payment\forms\forta\CreatePayRequest;
use app\services\payment\forms\forta\OutCardPayRequest;
use app\services\payment\forms\forta\PaymentRequest;
use app\services\payment\forms\forta\RefundPayRequest;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Faker\Provider\Base;
use Vepay\Gateway\Client\Validator\ValidationException;
use Yii;
use yii\helpers\Json;

class FortaTechAdapter implements IBankAdapter
{
    const AFT_MIN_SUMM = 120000;
    const BANK_URL = 'https://pay1time.com';
    const BANK_URL_TEST = 'https://pay1time.com';

    const REFUND_ID_CACHE_PREFIX = 'Forta__RefundId__';

    public static $bank = 9;
    protected $bankUrl;
    /** @var PartnerBankGate */
    protected $gate;

    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y') {
            $this->bankUrl = self::BANK_URL_TEST;
        } else {
            $this->bankUrl = self::BANK_URL;
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
        // TODO: Implement confirm() method.
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

    }

    /**
     * @inheritDoc
     */
    public function createPay(CreatePayForm $createPayForm)
    {
        $action = '/api/payments';
        $paySchet = $createPayForm->getPaySchet();
        $paymentRequest = new PaymentRequest();
        $paymentRequest->order_id = (string)$paySchet->ID;
        $paymentRequest->amount = $paySchet->getSummFull();
        $paymentRequest->processing_url = $paySchet->getOrderdoneUrl();
        $paymentRequest->return_url = $paySchet->getOrderdoneUrl();

        $ans = $this->sendRequest($action, $paymentRequest->getAttributes());
        if(!array_key_exists('id', $ans) || empty($ans['id'])) {
            throw new CreatePayException('FortaTechAdapter Empty ExtBillNumber');
        }

        $transId = $ans['id'];
        $action = '/payWithoutForm/' . $transId;
        $createPayRequest = new CreatePayRequest();
        $createPayRequest->cardNumber = (string)$createPayForm->CardNumber;
        $createPayRequest->cardHolder = $createPayForm->CardHolder;
        $createPayRequest->expireMonth = $createPayForm->CardMonth;
        $createPayRequest->expireYear = $createPayForm->CardYear;
        $createPayRequest->cvv = $createPayForm->CardCVC;

        $ans = $this->sendRequest($action, $createPayRequest->getAttributes());

        if(!array_key_exists('url', $ans) || empty($ans['url'])) {
            throw new CreatePayException('FortaTechAdapter Empty 3ds url');
        }
        $createPayResponse = new CreatePayResponse();
        $createPayResponse->isNeed3DSRedirect = true;
        $createPayResponse->isNeed3DSVerif = false;
        $createPayResponse->status = BaseResponse::STATUS_DONE;
        $createPayResponse->transac = (string)$transId;
        $createPayResponse->url = $ans['url'];

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
     * @throws BankAdapterResponseException
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        /** @var CheckStatusPayResponse $checkStatusPayResponse */
        $checkStatusPayResponse = null;
        if(Yii::$app->cache->exists(self::REFUND_ID_CACHE_PREFIX . $okPayForm->getPaySchet()->ID)) {
            $checkStatusPayResponse = $this->checkStatusPayRefund($okPayForm);
        } elseif ($okPayForm->getPaySchet()->uslugatovar->IsCustom == TU::$TOCARD) {
            $checkStatusPayResponse = $this->checkStatusPayOut($okPayForm);
        } else {
            $checkStatusPayResponse = $this->checkStatusPayDefault($okPayForm);
        }

        return $checkStatusPayResponse;
    }

    /**
     * @param OkPayForm $okPayForm
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    protected function checkStatusPayDefault(OkPayForm $okPayForm)
    {
        $ans = $this->sendGetStatusRequest($okPayForm->getPaySchet());

        $checkStatusPayResponse = new CheckStatusPayResponse();
        if(!array_key_exists('status', $ans) || empty($ans['status'])) {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = 'Ошибка проверки статуса';
            return $checkStatusPayResponse;
        }
        if(array_key_exists('error_description', $ans) && !empty($ans['error_description'])) {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = $ans['error_description'];
            return $checkStatusPayResponse;
        }
        $checkStatusPayResponse->status = $this->convertStatus($ans['status']);
        $checkStatusPayResponse->message = $ans['status'];

        return $checkStatusPayResponse;
    }

    /**
     * @param OkPayForm $okPayForm
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    protected function checkStatusPayOut(OkPayForm $okPayForm)
    {
        $ans = $this->sendGetStatusOutRequest($okPayForm->getPaySchet());

        $checkStatusPayResponse = new CheckStatusPayResponse();

        if($ans['status'] == true && isset($ans['data']['cards'][0]['transferParts'])) {
            // TODO: refact


            $transferParts = $ans['data']['cards'][0]['transferParts'];
            $errorData = '';
            $errorsCount = 0;
            $initCount = 0;
            $paidCount = 0;
            foreach ($transferParts as $transferPart) {
                if($transferPart['status'] == 'STATUS_ERROR') {
                    $errorData .= Json::encode($transferPart) . "\n";
                    $errorsCount++;
                } elseif ($transferPart['status'] == 'STATUS_INIT') {
                    $initCount++;
                } elseif ($transferPart['status'] == 'STATUS_PAID') {
                    $paidCount++;
                }
            }

            if(count($transferParts) === $paidCount) {
                // если все части платежей успешны
                $checkStatusPayResponse->status = BaseResponse::STATUS_DONE;
            } elseif (count($transferParts) === $errorsCount) {
                // если все части платежей с ошибкой
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            } elseif ($initCount === 0) {
                // если части платежей с ошибками и успешны
                $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
                $checkStatusPayResponse->message = mb_substr($errorData, 0, 250);
            } else {
                $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
            }
        } else {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = 'Ошибка запроса';
        }
        return $checkStatusPayResponse;
    }

    protected function checkStatusPayRefund(OkPayForm $okPayForm)
    {
        $ans = $this->sendGetStatusRefundRequest($okPayForm->getPaySchet());

        $checkStatusPayResponse = new CheckStatusPayResponse();
        if(isset($ans['status'])) {
            if($ans['status'] == 'STATUS_REFUND') {
                $checkStatusPayResponse->status = BaseResponse::STATUS_CANCEL;
                $checkStatusPayResponse->message = 'Возврат';
            } elseif ($ans['status'] == 'STATUS_ERROR' && isset($ans['message'])) {
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                $checkStatusPayResponse->message = $ans['message'];
            } else {
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                $checkStatusPayResponse->message = '';
            }
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
        $action = '/api/refund';
        $refundPayRequest = new RefundPayRequest();
        $refundPayRequest->payment_id = $refundPayForm->paySchet->ExtBillNumber;
        $ans = $this->sendRequest($action, $refundPayRequest->getAttributes());

        $refundPayResponse = new RefundPayResponse();
        if(array_key_exists('refund_id', $ans) && !empty($ans['refund_id'])) {
            Yii::$app->cache->set(
                self::REFUND_ID_CACHE_PREFIX . $refundPayForm->paySchet->ID,
                $ans['refund_id'],
                60 * 60 * 24 * 30
            );
            $refundPayResponse->status = BaseResponse::STATUS_CREATED;
            $refundPayResponse->message = isset($ans['status']) ? $ans['status'] : '';
        } else {
            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
            $refundPayResponse->message = isset($ans['message']) ? $ans['message'] : 'Ошибка запроса';
        }
        return $refundPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        $action = '/api/transferToCardv2';
        $outCardPayRequest = new OutCardPayRequest();
        $outCardPayRequest->orderId = (string)$outCardPayForm->paySchet->ID;
        $outCardPayRequest->cards = [
            [
                'card' => (string)$outCardPayForm->cardnum,
                'amount' => $outCardPayForm->amount,
            ]
        ];

        $signature = $this->buildSignatureByOutCardPay($outCardPayRequest);
        $ans = $this->sendRequest($action, $outCardPayRequest->getAttributes(), $signature);

        $outCardPayResponse = new OutCardPayResponse();
        if($ans['status'] == true && isset($ans['data']['id'])) {
            $outCardPayResponse->status = BaseResponse::STATUS_DONE;
            $outCardPayResponse->trans = $ans['data']['id'];
        } elseif ($ans['status'] == false && isset($ans['errors']['description'])) {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $ans['errors']['description'];
        } else {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;

            if(array_key_exists('errors', $ans) && isset($ans['errors'][0])) {
                $outCardPayResponse->message = $ans['errors'][0]['description'];
            } else {
                $outCardPayResponse->message = 'Ошибка запроса';
            }
        }
        return $outCardPayResponse;
    }

    /**
     * @param OutCardPayRequest $outCardPayRequest
     * @return string
     */
    protected function buildSignatureByOutCardPay(OutCardPayRequest $outCardPayRequest)
    {
        $s = sprintf(
            '%s;%s;%s;',
            $outCardPayRequest->orderId,
            $outCardPayRequest->cards[0]['card'],
            $outCardPayRequest->cards[0]['amount']
        );

        $hash = hash('sha256', $s, true);
        $resPrivateKey = openssl_pkey_get_private(
            'file://' . Yii::getAlias('@app/config/forta/' . $this->gate->Login . '.pem')
        );
        $signature = null;
        openssl_private_encrypt($hash, $signature, $resPrivateKey);
        return base64_encode($signature);
    }

    /**
     * @inheritDoc
     */
    public function getAftMinSum()
    {
        return self::AFT_MIN_SUMM;
    }

    /**
     * @param $uri
     * @param $data
     * @param string $signature
     * @return array
     * @throws BankAdapterResponseException
     */
    protected function sendRequest($uri, $data, $signature = '')
    {
        $curl = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Authorization: Token: ' . $this->gate->Token,
        ];

        if(!empty($signature)) {
            $headers[] = 'Signature: ' . $signature;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->bankUrl . $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => Json::encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $maskedRequest = $this->maskRequestCardInfo($data);
        Yii::warning('FortaTechAdapter req uri=' . $uri .' : ' . Json::encode($maskedRequest));
        $response = curl_exec($curl);
        Yii::warning('FortaTechAdapter response:' . $response);
        $curlError = curl_error($curl);
        Yii::warning('FortaTechAdapter curlError:' . $curlError);
        $info = curl_getinfo($curl);

        try {
            Yii::warning(sprintf(
                'FortaTechAdapter response: %s | curlError: %s | info: %s',
                $response,
                $curlError,
                Json::encode($info)
            ));
            $response = $this->parseResponse($response);
            $maskedResponse = $this->maskResponseCardInfo($response);
        } catch (\Exception $e) {
            throw new BankAdapterResponseException('Ошибка запроса');
        }

        if(empty($curlError) && ($info['http_code'] == 200 || $info['http_code'] == 201)) {
            Yii::warning('FortaTechAdapter ans uri=' . $uri .' : ' . Json::encode($maskedResponse));
            return $response;
        } elseif (isset($response['errors']['description'])) {
            Yii::error('FortaTechAdapter ans uri=' . $uri .' : ' . Json::encode($maskedResponse));
            return $response;
        } elseif ($response['result'] == false && isset($response['message'])) {
            Yii::error('FortaTechAdapter ans uri=' . $uri .' : ' . Json::encode($maskedResponse));
            return $response;
        } else {
            Yii::error('FortaTechAdapter error uri=' . $uri .' status=' . $info['http_code']);
            throw new BankAdapterResponseException('Ошибка запроса: ' . $curlError);
        }
    }

    /**
     * @param PaySchet $paySchet
     * @return array
     * @throws BankAdapterResponseException
     */
    protected function sendGetStatusRequest(PaySchet $paySchet)
    {
        $curl = curl_init();

        $url = sprintf(
            '%s/api/payments?order_id=%s&id=%s',
            $this->bankUrl,
            $paySchet->ID,
            $paySchet->ExtBillNumber
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Token: ' . $this->gate->Token,
            ),
        ));

        Yii::warning('FortaTechAdapter req uri=' . $url);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if(empty($curlError) && $info['http_code'] == 200) {
            $response = $this->parseResponse($response);
            $maskedResponse = $this->maskResponseCardInfo($response);
            Yii::warning('FortaTechAdapter ans uri=' . $url .' : ' . Json::encode($maskedResponse));
            return $response;
        } else {
            Yii::error('FortaTechAdapter error uri=' . $url .' status=' . $info['http_code']);
            throw new BankAdapterResponseException('Ошибка запроса: ' . $curlError);
        }
    }

    protected function sendGetStatusOutRequest(PaySchet $paySchet)
    {
        $curl = curl_init();

        $url = sprintf(
            '%s/api/transferToCardv2?orderId=%s',
            $this->bankUrl,
            $paySchet->ID
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Token: ' . $this->gate->Token,
            ),
        ));

        Yii::warning('FortaTechAdapter req uri=' . $url);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if(empty($curlError) && $info['http_code'] == 200) {
            $response = $this->parseResponse($response);
            $maskedResponse = $this->maskResponseCardInfo($response);
            Yii::warning('FortaTechAdapter ans uri=' . $url .' : ' . Json::encode($maskedResponse));
            return $response;
        } else {
            Yii::error('FortaTechAdapter error uri=' . $url .' status=' . $info['http_code']);
            throw new BankAdapterResponseException('Ошибка запроса: ' . $curlError);
        }
    }

    public function sendGetStatusRefundRequest(PaySchet $paySchet)
    {
        $curl = curl_init();

        $url = sprintf(
            '%s/api/refund?refund_id=%s',
            $this->bankUrl,
            Yii::$app->cache->get(self::REFUND_ID_CACHE_PREFIX . $paySchet->ID)
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Token: ' . $this->gate->Token,
            ),
        ));

        Yii::warning('FortaTechAdapter req uri=' . $url);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if (empty($curlError) && $info['http_code'] == 200) {
            $response = $this->parseResponse($response);
            $maskedResponse = $this->maskResponseCardInfo($response);
            Yii::warning('FortaTechAdapter ans uri=' . $url . ' : ' . Json::encode($maskedResponse));
            return $response;
        } else {
            Yii::error('FortaTechAdapter error uri=' . $url . ' status=' . $info['http_code']);
        }
    }

    /**
     * @param string $status
     * @return int
     */
    protected function convertStatus(string $status)
    {
        switch($status) {
            case 'STATUS_HOLD':
            case 'STATUS_INIT':
            case 'STATUS_CONFIRMATION':
                return BaseResponse::STATUS_CREATED;
            case 'STATUS_PAID':
                return BaseResponse::STATUS_DONE;
            case 'STATUS_ERROR':
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @param string $response
     * @return array
     */
    protected function parseResponse(string $response)
    {
        return Json::decode($response, true);
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

    public function identInit(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    private function maskRequestCardInfo(array $data): array
    {
        // CreatePayRequest model
        if (isset($data['cardNumber'])) {
            $data['cardNumber'] = $this->maskCardNumber($data['cardNumber']);
        }

        // CreatePayRequest model
        if (isset($data['cvv'])) {
            $data['cvv'] = '***';
        }

        // OutCardPayRequest model
        if (isset($data['cards']) && is_array($data['cards'])) {
            foreach ($data['cards'] as &$card) {
                $card['card'] = $this->maskCardNumber($card['card']);
            }
        }

        return $data;
    }

    private function maskResponseCardInfo(array $response): array
    {
        if (isset($response['data']) && isset($response['data']['cards'])) {
            foreach ($response['data']['cards'] as &$card) {
                $card['card'] = $this->maskCardNumber($card['card']);
            }
        }

        return $response;
    }

    private function maskCardNumber(string $cardNumber): string
    {
        return preg_replace('/(\d{6})(.+)(\d{4})/', '$1****$3', $cardNumber);
    }

    /**
     * @inheritDoc
     */
    public function identGetStatus(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }
}
