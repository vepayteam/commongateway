<?php

namespace app\services\payment\banks;

use app\models\payonline\Cards;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\bank_adapter_responses\SendP2pResponse;
use app\services\payment\banks\bank_adapter_responses\RegistrationBenificResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\data\ClientData;
use app\services\payment\CurlSSLStructure;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\BRSAdapterExeception;
use app\services\payment\exceptions\FailedRecurrentPaymentException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\brs\CheckStatusB2cRequest;
use app\services\payment\forms\brs\CheckStatusPayOutAccountRequest;
use app\services\payment\forms\brs\CheckStatusPayOutCardRequest;
use app\services\payment\forms\brs\CheckStatusPayRequest;
use app\services\payment\forms\brs\ConfirmP2pRequest;
use app\services\payment\forms\brs\CreatePayAftRequest;
use app\services\payment\forms\brs\CreatePayByRegCardRequest;
use app\services\payment\forms\brs\CreatePayRequest;
use app\services\payment\forms\brs\IXmlRequest;
use app\services\payment\forms\brs\OutCardPayCheckRequest;
use app\services\payment\forms\brs\OutCardPayRequest;
use app\services\payment\forms\brs\RecurrentPayRequest;
use app\services\payment\forms\brs\RefundPayRequest;
use app\services\payment\forms\brs\SendP2pRequest;
use app\services\payment\forms\brs\TransferToAccountRequest;
use app\services\payment\forms\brs\XmlRequest;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\helpers\BRSErrorHelper;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\Bank;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use Carbon\Carbon;
use Exception;
use Yii;
use yii\helpers\Json;

class BRSAdapter implements IBankAdapter
{
    const AFT_MIN_SUMM = 180000;
    const KEYS_PATH = '@app/config/brs/';

    const BALANCE_CARD_NUM = '5100920551403998'; // Карта используется для запроса баланса TODO: переместить в другое место?
    const BALANCE_FAKE_AMOUNT = 1000;

    const BRS_RESPONSE_SYSTEM_ERROR_CODE = 1001;

    public static $bank = 7;

    /** @var PartnerBankGate */
    protected $gate;

    protected $bankUrl;
    protected $bankP2pUrl;
    protected $bankUrl3DS;
    protected $bankP2pUrl3DS;
    protected $bankUrlB2C;

    protected $bankUrlXml;

    const BANK_URL = 'https://securepay.rsb.ru:9443';
    const BANK_URL_TEST = 'https://testsecurepay.rsb.ru:9443';

    const BANK_URL_3DS = 'https://securepay.rsb.ru/ecomm2/ClientHandler';
    const BANK_URL_3DS_TEST = 'https://testsecurepay.rsb.ru/ecomm2/ClientHandler';

    const BANK_URL_B2C = 'https://212.46.217.150:7603';
    const BANK_URL_B2C_TEST = 'https://212.46.217.150:7601';

    const BANK_URL_XML = 'https://194.67.29.215:8443';
    const BANK_URL_XML_TEST = 'https://194.67.29.216:8443';

    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        $config = Yii::$app->params['services']['payments']['BRS'];

        $this->bankUrl = $config['url'];
        $this->bankUrl3DS = $config['url_3ds'];
        $this->bankP2pUrl = $config['url_p2p'];
        $this->bankP2pUrl3DS = $config['url_p2p_3ds'];
        $this->bankUrlXml = $config['url_xml'];
        $this->bankUrlB2C = $config['url_b2c'];
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
        if($donePayForm->getPaySchet()->uslugatovar->IsCustom == UslugatovarType::P2P) {
            return $this->confirmP2p($donePayForm);
        }
        $confirmPayResponse = new ConfirmPayResponse();
        $confirmPayResponse->status = BaseResponse::STATUS_DONE;
        return $confirmPayResponse;
    }

    /**
     * @param DonePayForm $donePayForm
     * @return ConfirmPayResponse
     */
    protected function confirmP2p(DonePayForm $donePayForm)
    {
        $uri = '/ecomm2/MerchantHandler';
        $conformP2pRequest = new ConfirmP2pRequest();
        $conformP2pRequest->trans_id = $donePayForm->getPaySchet()->ExtBillNumber;
        $conformP2pRequest->client_ip_address = Yii::$app->request->remoteIP;

        $confirmPayResponse = new ConfirmPayResponse();
        try {
            $data = $conformP2pRequest->getAttributes();
            $ans = $this->sendRequest($uri, $data, $this->bankP2pUrl);
            if(array_key_exists('error', $ans)) {
                $confirmPayResponse->status = BaseResponse::STATUS_ERROR;
                $confirmPayResponse->message = $ans['error'];
            } else {
                $confirmPayResponse->status = BaseResponse::STATUS_DONE;
            }
        } catch (BankAdapterResponseException $e) {
            $confirmPayResponse->status = BaseResponse::STATUS_ERROR;
            $confirmPayResponse->message = BankAdapterResponseException::REQUEST_ERROR_MSG;
        }

        return $confirmPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function createPay(CreatePayForm $createPayForm, ClientData $clientData)
    {
        $uri = '/ecomm2/MerchantHandler';

        $paySchet = $createPayForm->getPaySchet();
        $createPayRequest = $this->buildCreatePayRequest($paySchet, $createPayForm);

        $createPayResponse = new CreatePayResponse();
        try {
            $data = $createPayRequest->getAttributes();
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
        } catch (BankAdapterResponseException $e) {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = BankAdapterResponseException::REQUEST_ERROR_MSG;
        }

        return $createPayResponse;
    }

    /**
     * @param PaySchet $paySchet
     * @param CreatePayForm $createPayForm
     * @return CreatePayByRegCardRequest|CreatePayRequest
     */
    protected function buildCreatePayRequest(PaySchet $paySchet, CreatePayForm $createPayForm)
    {
        /** @var CreatePayRequest $createPayRequest */
        if($paySchet->uslugatovar->ID == Uslugatovar::REG_CARD_ID || $paySchet->RegisterCard) {
            $createPayRequest = new CreatePayByRegCardRequest();
            $createPayRequest->biller_client_id = Yii::$app->security->generateRandomString();
            $createPayRequest->perspayee_expiry = Carbon::now()->addYears(3)->format('my');
        } elseif ($this->gate->TU == UslugatovarType::POGASHATF) {
            $createPayRequest = new CreatePayAftRequest();
        } else {
            $createPayRequest = new CreatePayRequest();
        }

        $createPayRequest->mrch_transaction_id = $paySchet->ID;
        $createPayRequest->amount = $paySchet->getSummFull();
        $createPayRequest->client_ip_addr = Yii::$app->request->remoteIP;
        $createPayRequest->cardname = $createPayForm->CardHolder;
        $createPayRequest->pan = $createPayForm->CardNumber;
        $createPayRequest->expiry = $createPayForm->CardYear . $createPayForm->CardMonth;
        $createPayRequest->cvc2 = $createPayForm->CardCVC;
        return $createPayRequest;
    }

    /**
     * @inheritDoc
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        $uslugatovar = $okPayForm->getPaySchet()->uslugatovar;
        if($uslugatovar->IsCustom == TU::$TOCARD) {
            return $this->checkStatusPayOutCard($okPayForm);
        } elseif ($uslugatovar->IsCustom == TU::$TOSCHET) {
            return $this->checkStatusPayOutSchet($okPayForm);
        } elseif ($uslugatovar->IsCustom == TU::$B2CSBP) {
            return $this->checkStatusB2c($okPayForm);
        }
        return $this->checkStatusPayBase($okPayForm);
    }

    /**
     * @param OkPayForm $okPayForm
     *
     * @return CheckStatusPayResponse
     */
    protected function checkStatusB2c(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $uri = '/eis-app/eis-rs/businessPaymentService/getB2cStatus';

        $requestData = $this->getCheckStatusB2cRequestData($okPayForm);
        $response = new CheckStatusPayResponse();

        try {
            $ans = $this->sendB2CRequest($uri, $requestData,'POST', $this->getTransferB2CRequestSslStructure());

            if (isset($ans['code']) && $ans['code'] == 0) {
                $response->status = BaseResponse::STATUS_DONE;
                $response->message = $ans['message'] ?? '';

                return $response;
            }

            $response->status = BaseResponse::STATUS_ERROR;
            $response->message = $ans['message'] ?? '';
        } catch (BankAdapterResponseException $e) {
            $response->status = BaseResponse::STATUS_ERROR;
            $response->message = $e->getMessage();
        }

        return $response;
    }

    /**
     * @deprecated
     * @param OkPayForm $okPayForm
     *
     * @return CheckStatusPayResponse
     */
    protected function checkStatusPayOutSchet(OkPayForm $okPayForm)
    {
        $uri = '/eis-app/eis-rs/businessPaymentService/getB2cStatus';
        $checkStatusPayOutAccountRequest = new CheckStatusPayOutAccountRequest();
        $checkStatusPayOutAccountRequest->sourceId = (string)$okPayForm->IdPay;
        $checkStatusPayOutAccountRequest->operationId = (string)$okPayForm->getPaySchet()->ExtBillNumber;

        $checkStatusPayResponse = new CheckStatusPayResponse();
        try {
            $ans = $this->sendB2CRequest( $uri, $checkStatusPayOutAccountRequest->getAttributes(), 'POST');
            if(isset($ans['code']) && $ans['code'] == 0) {
                $checkStatusPayResponse->status = BaseResponse::STATUS_DONE;
            } else {
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            }

            $checkStatusPayResponse->message = ($ans['message'] ?? '');
        } catch (BankAdapterResponseException $e) {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = BankAdapterResponseException::REQUEST_ERROR_MSG;
        }
        return $checkStatusPayResponse;
    }

    /**
     * @param OkPayForm $okPayForm
     * @return CheckStatusPayResponse
     */
    protected function checkStatusPayBase(OkPayForm $okPayForm)
    {
        $uri = '/ecomm2/MerchantHandler';

        $paySchet = $okPayForm->getPaySchet();
        $checkStatusPayRequest = new CheckStatusPayRequest();
        $checkStatusPayRequest->trans_id = $paySchet->ExtBillNumber;
        $checkStatusPayRequest->client_ip_addr = (Yii::$app instanceof \yii\web\Application) ? Yii::$app->request->remoteIP : '127.0.0.1';

        $checkStatusPayResponse = new CheckStatusPayResponse();
        try {
            $domain = $this->bankUrl;
            if($paySchet->OutCardPan) {
                $domain = $this->bankP2pUrl;
            }
            $ans = $this->sendRequest($uri, $checkStatusPayRequest->getAttributes(), $domain);
            $checkStatusPayResponse->message = BRSErrorHelper::getMessage($ans);
            $checkStatusPayResponse->status = $this->getStatusResponse($ans['RESULT']);
            $this->checkStatusPayResponseFiller($checkStatusPayResponse, $ans);
            $checkStatusPayResponse->rrn = (array_key_exists('RRN', $ans) ? $ans['RRN'] : '');
            $checkStatusPayResponse->rcCode = $ans['RESULT_CODE'] ?? null;
        } catch (BankAdapterResponseException $e) {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = BankAdapterResponseException::REQUEST_ERROR_MSG;
        }

        return $checkStatusPayResponse;
    }

    /**
     * @param OkPayForm $okPayForm
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    protected function checkStatusPayOutCard(OkPayForm $okPayForm)
    {
        $paySchet = $okPayForm->getPaySchet();
        $checkStatusPayOutCardRequest = new CheckStatusPayOutCardRequest();
        $checkStatusPayOutCardRequest->paymentid = $paySchet->ExtBillNumber;

        $ans = $this->sendXmlRequest($checkStatusPayOutCardRequest);
        $checkStatusPayResponse = new CheckStatusPayResponse();
        if(array_key_exists('error', $ans)) {
            $error = $ans['error']['code'] . ': ' . $ans['error']['description'];
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = $error;
            return $checkStatusPayResponse;
        }

        $checkStatusPayResponse->status = $this->getStatusXmlResponse($ans['container']['status']);
        $checkStatusPayResponse->message = $ans['container']['status'];
        $checkStatusPayResponse->rrn = $ans['container']['rrn'] ?? '';
        return $checkStatusPayResponse;
    }

    /**
     * @param CheckStatusPayResponse $checkStatusPayResponse
     * @param array $data
     */
    protected function checkStatusPayResponseFiller(CheckStatusPayResponse $checkStatusPayResponse, array $data)
    {
        if(array_key_exists('CARD_NUMBER', $data)) {
            $checkStatusPayResponse->cardNumber = $data['CARD_NUMBER'];
        }
        if(array_key_exists('RECC_PMNT_ID', $data)) {
            $checkStatusPayResponse->cardRefId = $data['RECC_PMNT_ID'];
        }
        if(array_key_exists('RECC_PMNT_EXPIRY', $data)) {
            $checkStatusPayResponse->expMonth = substr($data['RECC_PMNT_EXPIRY'], 0, 2);
            $checkStatusPayResponse->expYear = '20' . substr($data['RECC_PMNT_EXPIRY'], -2);
        }
    }

    /**
     * @inheritDoc
     */
    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        $uri = '/ecomm2/MerchantHandler';
        $paySchet = $autoPayForm->paySchet;

        $recurrentPayRequest = new RecurrentPayRequest();
        $recurrentPayRequest->amount = $paySchet->getSummFull();
        $recurrentPayRequest->client_ip_addr = (Yii::$app instanceof \yii\web\Application) ? Yii::$app->request->remoteIP : '127.0.0.1' ;
        $recurrentPayRequest->description = 'Оплата счета №' . $paySchet->ID;
        $recurrentPayRequest->biller_client_id = $autoPayForm->getCard()->ExtCardIDP;
        $recurrentPayRequest->mrch_transaction_id = $paySchet->ID;

        $createRecurrentPayResponse = new CreateRecurrentPayResponse;
        try {
            $ans = $this->sendRequest($uri, $recurrentPayRequest->getAttributes());

            if (isset($ans['RESULT_CODE']) && intval($ans['RESULT_CODE']) === self::BRS_RESPONSE_SYSTEM_ERROR_CODE) {
                Yii::error('BRSAdapter recurrentPay paySchet.ID=' . $paySchet->ID
                    . ' bad response result code 1001');

                // Вызываем FailedRecurrentPaymentException, чтобы в RecurrentPayJob платежу присвоился статус ERROR и не было опроса статуса
                throw new FailedRecurrentPaymentException(BRSErrorHelper::getMessage($ans), $ans['RESULT_CODE'], $ans['TRANSACTION_ID'] ?? '');
            }

            $createRecurrentPayResponse->message = BRSErrorHelper::getMessage($ans);
            $createRecurrentPayResponse->status = $this->getStatusResponse($ans['RESULT']);
            $createRecurrentPayResponse->transac = isset($ans['TRANSACTION_ID']) ? $ans['TRANSACTION_ID'] : '';
            $createRecurrentPayResponse->rrn = isset($ans['RRN']) ? $ans['RRN'] : '';
        } catch (BankAdapterResponseException $e) {
            $createRecurrentPayResponse->status = BaseResponse::STATUS_ERROR;
            $createRecurrentPayResponse->message = BankAdapterResponseException::REQUEST_ERROR_MSG;
        }
        return $createRecurrentPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function refundPay(RefundPayForm $refundPayForm)
    {
        $uri = '/ecomm2/MerchantHandler';

        $sourcePaySchet = $refundPayForm->paySchet->refundSource;

        $refundPayRequest = new RefundPayRequest();
        $refundPayRequest->trans_id = $sourcePaySchet->ExtBillNumber;

        if($sourcePaySchet->DateCreate < Carbon::now()->startOfDay()->timestamp) {
            $refundPayRequest->command = 'k';
        }

        $refundPayResponse = new RefundPayResponse();
        try {
            $ans = $this->sendRequest($uri, $refundPayRequest->getAttributes());
            $refundPayResponse->message = BRSErrorHelper::getMessage($ans);
            $refundPayResponse->status = $this->getStatusResponse($ans['RESULT']);
        } catch (BRSAdapterExeception $e) {
            $refundPayResponse->message = $e->getMessage();
            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
        }

        return $refundPayResponse;
    }

    /**
     * @param string $uri
     * @param array $data
     * @return array|string
     * @throws BankAdapterResponseException
     */
    protected function sendRequest(string $uri, array $data, string $domain=null)
    {
        $curl = curl_init();

        if(is_null($domain)) {
            $domain = $this->bankUrl;
        }

        $sslCertPath = Yii::getAlias(self::KEYS_PATH . $this->gate->Login . '.pem');
        $sslKeyPath = Yii::getAlias(self::KEYS_PATH . $this->gate->Login . '.key');
        $this->validateCertFiles($sslCertPath, $sslKeyPath);

        $url = $domain . $uri;
        $request = http_build_query($data);
        curl_setopt_array($curl, array(
            CURLOPT_VERBOSE => Yii::$app->params['VERBOSE'] === 'Y',
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_USERAGENT => (Yii::$app instanceof \yii\web\Application) ? Yii::$app->request->userAgent : '',
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSLCERT => $sslCertPath,
            CURLOPT_SSLKEY => $sslKeyPath,
            //            CURLOPT_CAINFO => Yii::getAlias(self::KEYS_PATH . 'chain-ecomm-ca-root-ca.crt'),
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => (Yii::$app->params['TESTMODE'] != 'Y'),
            CURLOPT_TIMEOUT => 120,
        ));

        $requestDataLog = Cards::MaskCardLog(Json::encode($data));
        Yii::warning('BRSAdapter req uri=' . $uri .' : ' . $requestDataLog);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if(empty($curlError) && $info['http_code'] == 200) {
            try {
                $response = $this->parseResponse($response);
            } catch (Exception $e) {
                Yii::warning('BRSAdapter error while parsing response: response=' . $response
                    . ' exception=' . $e->getMessage()
                );
            }

            Yii::warning('BRSAdapter ans uri=' . $uri .' : ' . Json::encode($response));
            return $response;
        } else {
            Yii::error('BRSAdapter error uri=' . $uri .' status=' . $info['http_code']);

            $errMsg = [];
            $errMsg[] = 'request=' . $request;
            $errMsg[] = 'login=' . $this->gate->Login;
            $errMsg[] = 'token=' . $this->gate->Token;
            $errMsg[] = 'curlError=' . $curlError;
            if ($response) {
                $errMsg[] = 'response=' . $response;
            }

            Yii::warning('BRSAdapter bad xml response: ' . join(' ', $errMsg));

            throw new BankAdapterResponseException(BankAdapterResponseException::setErrorMsg($curlError));
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

    /**
     * @param string $result
     * @return int
     */
    protected function getStatusResponse(string $result)
    {
        switch ($result) {
            case 'CREATED':
            case 'PENDING':
                return BaseResponse::STATUS_CREATED;
            case 'OK':
                return BaseResponse::STATUS_DONE;
            case 'REVERSED':
            case 'AUTOREVERSED':
                /**
                 * Для refund/reverse возвращать STATUS_DONE тк начальная транзакция остается в статусе успешно
                 */
                return BaseResponse::STATUS_DONE;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @param string $result
     * @return int
     */
    protected function getStatusXmlResponse(string $result)
    {
        switch ($result) {
            case 'active ':
                return BaseResponse::STATUS_CREATED;
            case 'finished':
                return BaseResponse::STATUS_DONE;
            case 'cancelled':
            case 'returned':
                /**
                 * Для refund/reverse возвращать STATUS_DONE тк начальная транзакция остается в статусе успешно
                 */
                return BaseResponse::STATUS_DONE;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @inheritDoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        $outCardPayCheckRequest = new OutCardPayCheckRequest();

        $outCardPayCheckRequest->card = $outCardPayForm->cardnum;
        $outCardPayCheckRequest->tr_date = Carbon::now()->format('YmdHis');
        $outCardPayCheckRequest->amount = $outCardPayForm->amount;
        $ans = $this->sendXmlRequest($outCardPayCheckRequest);

        $outCardPayResponse = new OutCardPayResponse();
        if(array_key_exists('error', $ans)) {
            $error = $ans['error']['code'] . ': ' . $ans['error']['description'];
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $error;
            return $outCardPayResponse;
        }
        $outCardPayForm->paySchet->ExtBillNumber = $ans['container']['paymentid'];
        $outCardPayForm->paySchet->save(false);

        $outCardPayRequest = new OutCardPayRequest();
        $outCardPayRequest->paymentid = $ans['container']['paymentid'];
        $outCardPayRequest->transaction_id = $outCardPayForm->paySchet->ID;
        $outCardPayRequest->amount = $outCardPayForm->amount;
        $outCardPayRequest->tr_date = Carbon::now()->format('YmdHis');

        $ans = $this->sendXmlRequest($outCardPayRequest);
        if(array_key_exists('error', $ans)) {
            $error = $ans['error']['code'] . ': ' . $ans['error']['description'];
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $error;
            return $outCardPayResponse;
        }
        $outCardPayResponse->status = BaseResponse::STATUS_DONE;
        $outCardPayResponse->message = '';
        $outCardPayResponse->trans = $ans['container']['paymentid'];
        return $outCardPayResponse;
    }

    /**
     * @param IXmlRequest $request
     * @return mixed
     * @throws BankAdapterResponseException
     */
    private function sendXmlRequest(IXmlRequest $request)
    {
        $xml = $request->buildXml($this->gate);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_VERBOSE => Yii::$app->params['VERBOSE'] === 'Y',
            CURLOPT_URL => $this->bankUrlXml,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml',
                'Accept: text/xml',
                'Accept-Encoding: *',
                'Pragma: no-cache',
                'User-Agent: Mozilla/4.0',
                'Cache-Control: no-cache',
                'Expect: 100-continue',
                'Authorization: Basic ' . base64_encode($this->gate->Token . ':' . $this->gate->Password)
            ),
        ));

        Yii::warning('BRSAdapter xmlReq uri=' . $xml);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if(empty($curlError) && $info['http_code'] == 200) {
            Yii::warning('BRSAdapter xmlAns uri=' . $response);

            try {
                $response = $this->parseXmlResponse($response);
            } catch (Exception $e) {
                Yii::warning('BRSAdapter error while parsing xml response: response=' . $response
                    . ' exception=' . $e->getMessage()
                );
            }

            return $response;
        } else {
            $errMsg = [];
            $errMsg[] = 'request=' . $xml;
            $errMsg[] = 'login=' . $this->gate->Login;
            $errMsg[] = 'token=' . $this->gate->Token;
            $errMsg[] = 'curlError=' . $curlError;
            if ($response) {
                $errMsg[] = 'response=' . $response;
            }

            Yii::warning('BRSAdapter bad xml response: ' . join(' ', $errMsg));

            throw new BankAdapterResponseException(BankAdapterResponseException::setErrorMsg($curlError));
        }
    }

    /**
     * @param string $xml
     * @return mixed
     */
    public function parseXmlResponse(string $xml)
    {
        $dom = simplexml_load_string($xml, "SimpleXMLElement", 0, 'rsb_ns', 'true');
        $response = json_decode(json_encode($dom), true);
        return $response;
    }

    /**
     * @return int
     */
    public function getAftMinSum()
    {
        return Bank::findOne(self::$bank)->AftMinSum ?? self::AFT_MIN_SUMM;
    }

    /**
     * Для запроса баланса используется outCardPayCheck запрос, тк у них нету отдельного эндпоинта для получения баланса
     * По логике outCardPayCheck запрос проверяет возможность перевода на карту и по своместитульству в ответе возвращает баланс партнера
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest): GetBalanceResponse
    {
        $outCardPayCheckRequest = new OutCardPayCheckRequest();
        $outCardPayCheckRequest->card = self::BALANCE_CARD_NUM;
        $outCardPayCheckRequest->amount = self::BALANCE_FAKE_AMOUNT;
        $outCardPayCheckRequest->tr_date = Carbon::now()->format('YmdHis');

        $answer = $this->sendXmlRequest($outCardPayCheckRequest);
        Yii::warning('BRSAdapter getBalance: PartnerId=' . $this->gate->PartnerId
            . ' GateId=' . $this->gate->Id
            . ' Request=' . Json::encode($outCardPayCheckRequest->getAttributes())
            . ' Response=' . Json::encode($answer)
        );
        if (array_key_exists('error', $answer)) {
            $error = $answer['error']['code'] . ': ' . $answer['error']['description'];

            throw new BankAdapterResponseException(
                BankAdapterResponseException::setErrorMsg($error)
            );
        }

        $balanceResponse = new GetBalanceResponse();
        $balanceResponse->bank_name = $getBalanceRequest->bankName;
        $balanceResponse->amount = PaymentHelper::convertToFullAmount(intval($answer['container']['partner_available_amount']));
        $balanceResponse->currency = $getBalanceRequest->currency;
        $balanceResponse->account_type = $getBalanceRequest->accountType;

        return $balanceResponse;
    }

    /**
     * @return mixed|null
     * @throws BankAdapterResponseException
     */
    public function getBankReceiver()
    {
        $uri = '/eis-app/eis-rs/businessPaymentService/getFpsReference';

        return $this->sendB2CRequest($uri, [], 'GET', $this->getTransferB2CRequestSslStructure());
    }

    /**
     * @inheritDoc
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        if($outPayaccForm->scenario != OutPayAccountForm::SCENARIO_FL) {
            throw new GateException('Перечисление денежны средств фозможно только для физ. лиц');
        }

        $uri = '/eis-app/eis-rs/businessPaymentService/requestTransferB2c';
        $transferToAccountRequest = new TransferToAccountRequest();
        $transferToAccountRequest->bic = $outPayaccForm->bic;
        $transferToAccountRequest->receiverId = (string)$outPayaccForm->paySchet->ID;
        $transferToAccountRequest->merchantId = $this->gate->Token;
        $transferToAccountRequest->firstName = $outPayaccForm->getFirstName();
        $transferToAccountRequest->lastName = $outPayaccForm->getLastName();
        $transferToAccountRequest->middleName = $outPayaccForm->getLastName();
        $transferToAccountRequest->amount = $outPayaccForm->amount;
        $transferToAccountRequest->account = (string)$outPayaccForm->account;
        $transferToAccountRequest->sourceId = (string)$outPayaccForm->paySchet->ID;

        if(Yii::$app->params['TESTMODE'] == 'Y') {
            $transferToAccountRequest->account = '40702810200000007194';
            $transferToAccountRequest->bic = '044525151';
            $transferToAccountRequest->receiverId = '0079167932356';
            $transferToAccountRequest->firstName = 'Максим';
            $transferToAccountRequest->lastName = 'Филин';
            $transferToAccountRequest->middleName = 'Сергеевич';
        }

        $requestData = $transferToAccountRequest->getAttributes();
        $requestData['msgSign'] = $transferToAccountRequest->getMsgSign($this->gate);
        $transferToAccountResponse = new TransferToAccountResponse();

        try {
            $ans = $this->sendB2CRequest($uri, $requestData, 'POST');
            if(isset($ans['code']) && $ans['code'] == 0) {
                $transferToAccountResponse->status = BaseResponse::STATUS_DONE;
                $transferToAccountResponse->message = $ans['message'];
                $transferToAccountResponse->trans = ($ans['operationId'] ?? '');
            } else {
                $transferToAccountResponse->status = BaseResponse::STATUS_DONE;
                $transferToAccountResponse->message = ($ans['message'] ?? BankAdapterResponseException::REQUEST_ERROR_MSG);
            }
        } catch (BankAdapterResponseException $e) {
            $transferToAccountResponse->status = BaseResponse::STATUS_ERROR;
            $transferToAccountResponse->message = $e->getMessage();
        }
        return $transferToAccountResponse;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param string $requestType
     * @param CurlSSLStructure|null $curlSSLStructure
     *
     * @return mixed
     * @throws BankAdapterResponseException
     */
    protected function sendB2CRequest(string $uri, array $data = [], string $requestType = 'GET', ?CurlSSLStructure $curlSSLStructure = null)
    {
        $curl = curl_init();

        $optArray = [
            CURLOPT_VERBOSE => Yii::$app->params['VERBOSE'] === 'Y',
            CURLOPT_URL => $this->bankUrlB2C . $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $requestType,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-User-Login: ' . $this->gate->AdvParam_1,
            ],
        ];

        if ($requestType !== 'GET') {
            if (!empty($data)) {
                $optArray[CURLOPT_POSTFIELDS] = Json::encode($data);
            }
        } else {
            $uri .= !empty($data) ? '?' . http_build_query($data) : '';
        }

        if ($curlSSLStructure instanceof CurlSSLStructure) {
            $this->validateCertFiles($curlSSLStructure->sslcert, $curlSSLStructure->sslkey);

            $optArray[CURLOPT_SSLCERTTYPE] = $curlSSLStructure->sslcerttype;
            $optArray[CURLOPT_SSLKEYTYPE] = $curlSSLStructure->sslkeytype;
            $optArray[CURLOPT_SSLCERT] = $curlSSLStructure->sslcert;
            $optArray[CURLOPT_SSLKEY] = $curlSSLStructure->sslkey;
        } else {
            $sslCertPath = Yii::getAlias(self::KEYS_PATH . $this->gate->Login . '.pem');
            $sslKeyPath = Yii::getAlias(self::KEYS_PATH . $this->gate->Login . '.key');
            $this->validateCertFiles($sslCertPath, $sslKeyPath);

            $optArray[CURLOPT_SSLCERTTYPE] = 'PEM';
            $optArray[CURLOPT_SSLKEYTYPE] = 'PEM';
            $optArray[CURLOPT_SSLCERT] = $sslCertPath;
            $optArray[CURLOPT_SSLKEY] = $sslKeyPath;
        }

        curl_setopt_array($curl, $optArray);

        Yii::warning('BRSAdapter req ' . $requestType . ' uri=' . $uri . '; data=' . Json::encode($data));
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if(empty($curlError)) {
            try {
                $response = Json::decode($response, true);
                Yii::warning('BRSAdapter ans ' . $requestType . ' uri=' . $uri .' : ' . Json::encode($response) . '; data=' . Json::encode($data));
                return $response;
            } catch (Exception $e) {
                throw new BankAdapterResponseException($e->getMessage());
            }
        } else {
            Yii::error('BRSAdapter curlError ' . $requestType . ' uri=' . $uri .'; info=' . json_encode($info) . '; curlError=' . $curlError);
            Yii::error('BRSAdapter error ' . $requestType . ' uri=' . $uri .'; status=' . $info['http_code'] . '; data=' . Json::encode($data));
            throw new BankAdapterResponseException(BankAdapterResponseException::setErrorMsg($curlError));
        }
    }

    /**
     * @return CurlSSLStructure
     */
    private function getTransferB2CRequestSslStructure(): CurlSSLStructure
    {
        $sslData = new CurlSSLStructure();

        $sslData->sslcerttype = 'PEM';
        $sslData->sslkeytype = 'PEM';
        $sslData->sslcert = Yii::getAlias(self::KEYS_PATH . $this->gate->Login . '.pem');
        $sslData->sslkey = Yii::getAlias(self::KEYS_PATH . $this->gate->Login . '.key');

        return $sslData;
    }

    /**
     * @param OutPayAccountForm $outPayaccForm
     * @return bool
     * @throws \yii\base\Exception
     */
    public function checkTransferB2C(OutPayAccountForm $outPayaccForm): TransferToAccountResponse
    {
        $uri = '/eis-app/eis-rs/businessPaymentService/checkTransferB2c';

        $requestData = $this->getTransferB2cRequestData($outPayaccForm);
        $response = new TransferToAccountResponse();

        try {
            $ans = $this->sendB2CRequest($uri, $requestData,'POST', $this->getTransferB2CRequestSslStructure());

            if (isset($ans['code']) && $ans['code'] == 0) {
                $response->status = BaseResponse::STATUS_DONE;
                $response->message = $ans['message'] ?? '';
                $response->trans = $ans['operationId'];

                return $response;
            }

            $response->status = BaseResponse::STATUS_ERROR;
            $response->message = $ans['message'] ?? '';
            $response->trans = null;
        } catch (BankAdapterResponseException $e) {
            $response->status = BaseResponse::STATUS_ERROR;
            $response->message = $e->getMessage();
            $response->trans = null;
        }

        return $response;
    }

    /**
     * @param OutPayAccountForm $outPayaccForm
     * @return array
     */
    private function getTransferB2cRequestData(OutPayAccountForm $outPayaccForm): array
    {
        $id = $outPayaccForm->paySchet->ID ?? Yii::$app->security->generateRandomString(16);
        $transferToAccountRequest = new TransferToAccountRequest();
        $transferToAccountRequest->bic = $outPayaccForm->bic;
        $transferToAccountRequest->receiverId = $outPayaccForm->getPhoneToSend();
        $transferToAccountRequest->merchantId = $this->gate->Login;
        $transferToAccountRequest->firstName = $outPayaccForm->getFirstName();
        $transferToAccountRequest->lastName = $outPayaccForm->getLastName();
        $transferToAccountRequest->middleName = $outPayaccForm->getMiddleName();
        $transferToAccountRequest->amount = $outPayaccForm->amount;
        $transferToAccountRequest->account = $outPayaccForm->account;
        $transferToAccountRequest->sourceId = $id;

        $requestData = $transferToAccountRequest->getAttributes();
        $requestData['msgSign'] = $transferToAccountRequest->getMsgSign($this->gate, $this->gate->Login . '.key');

        return $requestData;
    }

    /**
     * @param OkPayForm $okPayForm
     *
     * @return array
     */
    private function getCheckStatusB2cRequestData(OkPayForm $okPayForm): array
    {
        $transferToAccountRequest = new CheckStatusB2cRequest();
        $transferToAccountRequest->sourceId = (string)$okPayForm->getPaySchet()->ID;
        $transferToAccountRequest->operationId = $okPayForm->getPaySchet()->ExtBillNumber;

        return $transferToAccountRequest->getAttributes();
    }

    /**
     * @param OutPayAccountForm $outPayaccForm
     * @return TransferToAccountResponse
     * @throws \yii\base\Exception
     */
    public function transferB2C(OutPayAccountForm $outPayaccForm): TransferToAccountResponse
    {
        $uri = '/eis-app/eis-rs/businessPaymentService/requestTransferB2c';

        $requestData = $this->getTransferB2cRequestData($outPayaccForm);

        $response = new TransferToAccountResponse();

        try {
            $ans = $this->sendB2CRequest($uri, $requestData, 'POST', $this->getTransferB2CRequestSslStructure());
            if(isset($ans['code']) && $ans['code'] == 0) {
                $response->status = BaseResponse::STATUS_DONE;
                $response->message = $ans['message'] ?? '';
                $response->trans = $ans['operationId'];

                return $response;
            }

            $response->status = BaseResponse::STATUS_ERROR;
            $response->message = $ans['message'] ?? '';
            $response->trans = null;
        } catch (BankAdapterResponseException $e) {
            $response->status = BaseResponse::STATUS_ERROR;
            $response->message = $e->getMessage();
            $response->trans = null;
        }

        return $response;
    }

    public function identInit(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function identGetStatus(Ident $ident)
    {
        // TODO: Implement ident() method.
    }

    /**
     * @throws GateException
     */
    public function currencyExchangeRates()
    {
        throw new GateException('Метод недоступен');
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        $uri = '/ecomm2/MerchantHandler';

        $paySchet = $sendP2pForm->paySchet;
        $sendP2pRequest = new SendP2pRequest();
        $sendP2pRequest->amount = $paySchet->getSummFull();
        $sendP2pRequest->currency = $paySchet->currency->Number;
        $sendP2pRequest->client_ip_addr = Yii::$app->request->remoteIP;
        $sendP2pRequest->cardname = $sendP2pForm->cardHolder;
        $sendP2pRequest->pan = $sendP2pForm->cardPan;
        $sendP2pRequest->expiry = substr($sendP2pForm->cardExpYear, 2)
            . sprintf('%02d', $sendP2pForm->cardExpMonth);
        $sendP2pRequest->cvc2 = $sendP2pForm->cvv;
        $sendP2pRequest->pan2 = $sendP2pForm->outCardPan;

        $sendP2pResponse = new SendP2pResponse();
        try {
            $data = $sendP2pRequest->getAttributes();
            $ans = $this->sendRequest($uri, $data, $this->bankP2pUrl);
            if(array_key_exists('error', $ans)) {
                $sendP2pResponse->status = BaseResponse::STATUS_ERROR;
                $sendP2pResponse->message = $ans['error'];
            } else {
                $sendP2pResponse->status = BaseResponse::STATUS_DONE;
                $sendP2pResponse->transac = $ans['TRANSACTION_ID'];
                $sendP2pResponse->url = $this->bankP2pUrl3DS . '?trans_id=' . urlencode($ans['TRANSACTION_ID']);
            }
        } catch (BankAdapterResponseException $e) {
            $sendP2pResponse->status = BaseResponse::STATUS_ERROR;
            $sendP2pResponse->message = BankAdapterResponseException::REQUEST_ERROR_MSG;
        }

        return $sendP2pResponse;
    }

    /**
     * @param string $sslCertPath
     * @param string $sslKeyPath
     * @param string|null $caInfoPath
     * @throws BankAdapterResponseException
     */
    private function validateCertFiles(string $sslCertPath, string $sslKeyPath, ?string $caInfoPath = null)
    {
        if (!file_exists($sslCertPath)) {
            Yii::error(
                'BRSAdapter validate cert files ssl cert file not found'
                . " gateLogin={$this->gate->Login}"
                . " partnerId={$this->gate->PartnerId}"
                . " fullPath=$sslCertPath"
            );
            throw new BankAdapterResponseException(BankAdapterResponseException::REQUEST_ERROR_MSG);
        }

        if (!file_exists($sslKeyPath)) {
            Yii::error(
                'BRSAdapter validate cert files ssl key file not found'
                . " gateLogin={$this->gate->Login}"
                . " partnerId={$this->gate->PartnerId}"
                . " fullPath=$sslKeyPath"
            );
            throw new BankAdapterResponseException(BankAdapterResponseException::REQUEST_ERROR_MSG);
        }

        if ($caInfoPath && !file_exists($caInfoPath)) {
            Yii::error(
                'BRSAdapter validate cert files ssl ca file not found'
                . " gateLogin={$this->gate->Login}"
                . " partnerId={$this->gate->PartnerId}"
                . " fullPath=$caInfoPath"
            );
            throw new BankAdapterResponseException(BankAdapterResponseException::REQUEST_ERROR_MSG);
        }
    }

    /**
     * @inheritDoc
     */
    public function registrationBenific(RegistrationBenificForm $registrationBenificForm)
    {
        throw new GateException('Метод недоступен');
    }
}
