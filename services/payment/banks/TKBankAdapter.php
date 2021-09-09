<?php


namespace app\services\payment\banks;

use app\models\mfo\MfoReq;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\models\queue\BinBDInfoJob;
use app\models\TU;
use app\services\ident\IdentService;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\Check3DSVersionResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetStatementsResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
use app\services\payment\banks\bank_adapter_responses\IdentInitResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\interfaces\ITKBankAdapterResponseErrors;
use app\services\payment\banks\traits\TKBank3DSTrait;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\MerchantRequestAlreadyExistsException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\RefundPayException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CheckStatusPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\CreatePaySecondStepForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\GetStatementsForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\tkb\CheckStatusPayRequest;
use app\services\payment\forms\tkb\Confirm3DSv2Request;
use app\services\payment\forms\tkb\CreatePayRequest;
use app\services\payment\forms\tkb\CreateRecurrentPayRequest;
use app\services\payment\forms\tkb\DonePay3DSv2Request;
use app\services\payment\forms\tkb\DonePayRequest;
use app\services\payment\forms\tkb\GetStatementRequest;
use app\services\payment\forms\tkb\OutCardPayRequest;
use app\services\payment\forms\tkb\RefundPayRequest;
use app\services\payment\forms\tkb\TransferToAccountRequest;
use app\services\payment\interfaces\Cache3DSv2Interface;
use app\services\payment\interfaces\Issuer3DSVersionInterface;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\exceptions\reRequestingStatusException;
use app\services\payment\exceptions\reRequestingStatusOkException;
use Carbon\Carbon;
use Faker\Provider\Base;
use qfsx\yii2\curl\Curl;
use SimpleXMLElement;
use Yii;
use yii\helpers\Json;

class TKBankAdapter implements IBankAdapter
{
    use TKBank3DSTrait;

    const AFT_MIN_SUMM = 185000;

    public const BIC = '044525388';
    const BANK_URL = 'https://pay.tkbbank.ru';
    const BANK_URL_TEST = 'https://paytest.online.tkbbank.ru';

    const BANK_URL_XML = 'https://193.232.101.14:8204';
    const BANK_URL_XML_TEST = 'https://193.232.101.14:8203';

    /** @var PartnerBankGate */
    protected $gate;

    private $bankUrl;
    private $bankUrlXml;
    private $shopId;
    private $UserCert;
    private $UserKey;
    private $keyFile;
    private $backUrls = ['ok' => 'https://api.vepay.online/pay/orderok?orderid='];

    public static $bank = 2;
    private $type = 0;
    private $IsCard = 0;
    private $IsAft = 0;

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;

        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y') {
            $this->bankUrl = self::BANK_URL_TEST;
            $this->bankUrlXml = self::BANK_URL_XML_TEST;
        } else {
            $this->bankUrl = self::BANK_URL;
            $this->bankUrlXml = self::BANK_URL_XML;
        }
    }

    /**
     * @return int
     */
    public function getBankId()
    {
        return self::$bank;
    }

    /**
     * Получение № тарнзакции в банке
     * @param array $data [IdPay]
     * @param User|null $user
     * @param int $idCard
     * @param int $activate
     * @return array ['tisket', 'recurrent']
     * @throws \yii\db\Exception
     */
    private function createTisket($data, $user = null, $idCard = 0, $activate = 0)
    {
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($data['IdPay']);

        $tisket = $userUrl = '';
        $isRecurrent = 0;
        if ($params && $params['Status'] == 0) {

            $order_description = 'Счет №' . $params['ID'];

            $card = null;
            if ($user && $idCard >= 0) {
                $card = $payschets->getSavedCard($user->ID, $idCard, $activate);
            }

            $emailClient = '';
            if (isset($params['Email']) && !empty($params['Email'])) {
                $emailClient = $params['Email'];
            } elseif (isset($data['email']) && !empty($data['email']) && $data['email'] != 'undefined') {
                $emailClient = $data['email'];
            }

            $queryData = [
                'OrderID' => $params['ID'],
                'Amount' => $params['SummFull'],
                'Description' => $order_description,
                'ClientInfo' => [
                    //'PhoneNumber'
                    'Email' => $emailClient,
                    //'FIO'
                ],
                'ReturnUrl' => $this->backUrls['ok'] . $params['ID'],
                'ShowReturnButton' => false,
                'TTL' => '00.00:' . $params['TimeElapsed'] . ':00',
                //'AdditionalParameters'
            ];

            if ($user && $idCard == -1) {
                //привязка карты
                $action = "/api/tcbpay/gate/registercardbegin";
            } elseif ($card && $idCard >= 0) {
                //реккурентный платеж с карты
                $action = "/api/tcbpay/gate/registerdirectorderfromregisteredcard";
                $isRecurrent = 1;
                $queryData['CardRefID'] = $card['ExtCardIDP'];
            } else {
                //оплата без привязки карты
                $action = "/api/tcbpay/gate/registerorderfromunregisteredcard";
            }

            $queryData = Json::encode($queryData);

            //$language = 'fullsize';
            // определяем через что зашли - pageView = MOBILE
            //if ($user->isMobile()) {
            //$language = 'mob';
            //}*/

            $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

            if (isset($ans['xml']) && !empty($ans['xml'])) {
                $xml = $this->parseAns($ans['xml']);
                if (isset($xml['Status']) && $xml['Status'] == '0') {
                    $userUrl = isset($xml['formurl']) ? $xml['formurl'] : '';
                    $tisket = $xml['ordernumber'];
                    //сохранение номера транзакции
                    $payschets = new Payschets();
                    $payschets->SetBankTransact([
                        'idpay' => $params['ID'],
                        'trx_id' => $tisket,
                        'url' => $userUrl
                    ]);

                    Yii::$app->session['IdPay'] = $params['ID'];
                }
            }
        }

        return ['tisket' => $tisket, 'recurrent' => $isRecurrent, 'url' => $userUrl];
    }

    /**
     * @param PaySchet $paySchet
     * @throws BankAdapterResponseException
     * @throws reRequestingStatusException
     * @throws reRequestingStatusOkException
     */
    public function reRequestingStatus( PaySchet $paySchet):void
    {
        $action = '/api/tcbpay/gate/getorderstate';

        $checkStatusPayRequest = new CheckStatusPayRequest();
        $checkStatusPayRequest->OrderID = $paySchet->ID;

        $queryData = Json::encode($checkStatusPayRequest->getAttributes());
        $response = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        Yii::warning("checkStatusOrder: " . $this->logArr($response), 'merchant');
        if (isset($response['xml']) && !empty($response['xml'])) {
            $xml = $this->parseAns($response['xml']);
            if ($xml && isset($xml['errorinfo']['errorcode']) && $xml['errorinfo']['errorcode'] == 1) {
                throw new reRequestingStatusOkException('В обработке');
            } else {
                $status = $this->convertState($xml);

                if(!in_array($status, BaseResponse::STATUSES)) {
                    throw new BankAdapterResponseException('Ошибка преобразования статусов');
                }
                if(isset($xml['orderinfo']['statedescription'])) {
                    $msg = $xml['orderinfo']['statedescription'];
                } else {
                    $msg = '';
                }
                switch ($status) {
                    case BaseResponse::STATUS_CREATED:
                        throw new reRequestingStatusException($msg);
                    case BaseResponse::STATUS_DONE:
                        throw new reRequestingStatusOkException($msg);
                    case BaseResponse::STATUS_ERROR :
                        throw new reRequestingStatusOkException($msg);
                    case BaseResponse::STATUS_CANCEL :
                        throw new reRequestingStatusOkException($msg);
                    default:
                        throw new BankAdapterResponseException('Ошибка запроса, попробуйте повторить позднее');
                }
            }
        } else {
            throw new BankAdapterResponseException('Ошибка запроса, попробуйте повторить позднее');
        }
    }

    /**
     * Проверка статуса заказа
     * @param array $params [ID, IsCustom]
     * @param bool $isCron
     * @return array [state, xml]
     */
    private function checkStatusOrder($params, $isCron)
    {
        //$action = '/api/v1/order/state';
        $action = '/api/tcbpay/gate/getorderstate';

        $queryData = [
            'OrderID' => $params['ID'],
            //'ExtId' => $params['ID']
        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);
        Yii::warning("checkStatusOrder: " . $this->logArr($ans), 'merchant');
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if ($xml && isset($xml['errorinfo']['errorcode']) && $xml['errorinfo']['errorcode'] == 1) {
                if ($isCron &&
                    isset($params['IsCustom']) && TU::IsInPay($params['IsCustom']) &&
                    mb_strripos($xml['errorinfo']['errormessage'], 'не существует') !== false
                ) {
                    //не найден в банке - если в кроне запрос, то отменить
                    return ['state' => 2, 'xml' => ['orderinfo' => ['statedescription' => 'Платеж не проведен']]];
                } else {
                    return ['state' => 0, 'xml' => ['orderinfo' => ['statedescription' => 'В обработке']]];
                }

            } else {
                $status = $this->convertState($xml);
                return ['state' => $status, 'xml' => $xml];
            }
        } elseif (
            $isCron &&
            isset($params['IsCustom']) && TU::IsInPay($params['IsCustom']) &&
            isset($ans['httperror']) && !empty($ans['httperror']) &&
            @$ans['httperror']['Code'] === "OrderNotExist") {
            //не найден в банке - если в кроне запрос, то отменить
            return ['state' => 2, 'xml' => ['orderinfo' => ['statedescription' => 'Платеж не проведен']]];
        }
        return ['state' => 0];
    }

    /**
     * Статус в наш - 1 - оплачен 2,3 - не оплачен
     * @param array $result
     * @return int
     */
    private function convertState($result)
    {
        return $this->convertStatePay($result);
    }

    /**
     * Статус в наш - 1 - оплачен 2,3 - не оплачен
     * @param array $result
     * @return int
     */
    private function convertStateCommon($result)
    {
        $status = 0;
        if (isset($result['orderinfo'])) {
            if (($result['orderinfo']['state'] == '3' || ($result['orderinfo']['state'] == '5' && $this->IsCard) || $result['orderinfo']['state'] == '0') && $result['Status'] == '0') {
                //Исполнен
                $status = 1;
            } elseif ($result['orderinfo']['state'] == '6') {
                //отказ в оплате
                $status = 2;
            } elseif ($result['orderinfo']['state'] == '5' && !$this->IsCard) {
                //Возврат
                $status = 3;
            } elseif ($result['orderinfo']['state'] == '8') {
                //Возврат
                $status = 3;
            } else {
                //Обрабатывается
                $status = 0;
            }
        }
        return $status;
    }

    /**
     * Статус в наш - 1 - оплачен 2,3 - не оплачен
     * @param array $result
     * @return int
     */
    private function convertStatePay($result)
    {
        Yii::warning('TKBankAdapter convertStatePay start: ' . Json::encode($result), 'merchant');
        $status = 0;
        if (isset($result['orderinfo'])) {
            switch ((int)$result['orderinfo']['state']) {
                case 1:
                    $status = BaseResponse::STATUS_CREATED;
                    break;
                case 3:
                case 0:
                case 2:
                    $status = BaseResponse::STATUS_DONE;
                    break;
                case 6:
                    $status = BaseResponse::STATUS_ERROR;
                    break;
                case 5:
                case 8:
                    $status = BaseResponse::STATUS_CANCEL;
                    break;
                default:
                    $status = BaseResponse::STATUS_CREATED;
            }
        }

        Yii::warning('TKBankAdapter convertStatePay finishStatus: ' . $status, 'merchant');
        return $status;
    }

    /**
     * Статус в наш - 1 - оплачен 2,3 - не оплачен
     * @param array $result
     * @return int
     */
    private function convertStateCard($result)
    {
        $status = 0;
        if (isset($result['orderinfo'])) {
            if (($result['orderinfo']['state'] == '5') && $result['Status'] == '0') {
                //Исполнен
                $status = 1;
            } elseif ($result['orderinfo']['state'] == '0') {
                //Исполнен
                $status = 1;
            } elseif ($result['orderinfo']['state'] == '6') {
                //отказ в оплате
                $status = 2;
            } else {
                //Обрабатывается
                $status = 0;
            }
        }
        return $status;
    }

    /**
     * Статус в наш - 1 - оплачен 2,3 - не оплачен
     * @param array $result
     * @return int
     */
    private function convertStateOut($result)
    {
        $status = 0;
        if (isset($result['orderinfo'])) {
            if (($result['orderinfo']['state'] == '0') && $result['Status'] == '0') {
                //Исполнен
                $status = 1;
            } elseif ($result['orderinfo']['state'] == '0') {
                //Исполнен
                $status = 1;
            } elseif ($result['orderinfo']['state'] == '6') {
                //отказ в оплате
                $status = 2;
            } else {
                //Обрабатывается
                $status = 0;
            }
        }
        return $status;
    }

    /**
     * Отправка POST запроса в банк
     * @param string $post
     * @param string $url
     * @param array $addHeader
     * @param bool $jsonReq
     * @return array [xml, error]
     */
    private function curlXmlReq($post, $url, $addHeader = [], $jsonReq = true)
    {

        $timout = 110;

        $curl = new Curl();
        Yii::warning("req: login = " . $this->gate->Login . " url = " . $url . "\r\n" . Cards::MaskCardLog($post), 'merchant');
        try {
            $curl->reset()
                ->setOption(CURLOPT_TIMEOUT, $timout)
                ->setOption(CURLOPT_CONNECTTIMEOUT, $timout)
                ->setOption(CURLOPT_HTTPHEADER, array_merge([
                        $jsonReq ? 'Content-type: application/json' : 'Content-Type: application/soap+xml; charset=utf-8',
                        'TCB-Header-Login: ' . $this->gate->Login,
                        'TCB-Header-Sign: ' . $this->HmacSha1($post, $this->gate->Token),
                        'TCB-Header-SerializerType: LowerCase'
                    ], $addHeader))
                ->setOption(CURLOPT_SSL_VERIFYHOST, false)
                ->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv1')
                ->setOption(CURLOPT_SSL_VERIFYPEER, false)
                //->setOption(CURLOPT_CAINFO, $this->caFile)
                ->setOption(CURLOPT_POSTFIELDS, $post);
            if (!empty($this->UserKey) && mb_stripos($url, $this->bankUrlXml) !== false) {
                $curl
                    ->setOption(CURLOPT_SSLKEY, $this->UserKey)
                    ->setOption(CURLOPT_SSLCERT, $this->UserCert);
            }
            Yii::warning("req startReq: login = " . $this->gate->Login . " url = " . $url . "\r\n" . Cards::MaskCardLog($post), 'merchant');
            $curl->post($url);
            Yii::warning("req finishReq: login = " . $this->gate->Login . " url = " . $url . "\r\n" . Cards::MaskCardLog($post), 'merchant');

        } catch (\Exception $e) {
            Yii::warning("req curlerror: login = " . $this->gate->Login . " url = " . $url . "\r\n" . Cards::MaskCardLog($post), 'merchant');
            Yii::warning("curlerror: " . $curl->responseCode . ":" . Cards::MaskCardLog($curl->response), 'merchant');
            $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
            return $ans;
        }

        //Yii::warning("Headers: " .print_r($curl->getRequestHeaders(), true), 'merchant');

        $ans = [];
        Yii::warning("curlcode: " . $curl->errorCode, 'merchant');
        Yii::warning("curlans: " . $curl->responseCode . ":" . Cards::MaskCardLog($curl->response), 'merchant');
        try {
            switch ($curl->responseCode) {
                case 200:

                case 202:
                    $ans['xml'] = $jsonReq ? Json::decode($curl->response) : $curl->response;
                    break;
                case 500:
                    $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
                    $ans['httperror'] = $jsonReq ? Json::decode($curl->response) : $curl->response;
                    break;
                default:
                    $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
                    break;
            }
        } catch (\yii\base\InvalidArgumentException $e) {
            $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
            $ans['httperror'] = $curl->response;
            return $ans;
        }

        return $ans;
    }

    private function logArr($arr)
    {
        if (Yii::$app->params['TESTMODE'] == 'Y' || Yii::$app->params['DEVMODE'] == 'Y') {
            $log = print_r($arr, true);
            if (preg_match('/\[CardNumberHash\]\s*=>\s*(\w+)/ius', $log, $m)) {
                $log = str_ireplace($m[1], "***", $log);
            }
            return $log;
        }
        return "";
    }

    /**
     * Парсинг ответа
     * @param array $resp
     * @return array
     */
    private function parseAns($resp)
    {
        $ret = self::array_change_key_case_recursive($resp, CASE_LOWER);

        if (isset($ret['errorinfo'])) {
            $ret['Status'] = $ret['errorinfo']['errorcode'];
        }

        return $ret;
    }

    private static function array_change_key_case_recursive($array, $case)
    {
        $array = array_change_key_case($array, $case);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::array_change_key_case_recursive($value, $case);
            }
        }
        return $array;
    }

    private function HmacSha1($post, $keyFile)
    {
        return base64_encode(hash_hmac('SHA1', $post, $keyFile, true));
    }

    /**
     * Баланс счета
     * @param array $params
     * @return array
     */
    public function getBalanceAcc(array $params)
    {
        $action = '/api/v1/banking/account/balance';

        $queryData = [
            'Account' => $params['account'],
        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);
        Yii::warning('TKBankAdapter getBalance: PartnerId=' . $this->gate->PartnerId
            . ' GateId=' . $this->gate->Id
            . ' Request=' . $queryData
            . ' Response=' . Json::encode($ans)
        );

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['amount'])) {
                return ['status' => 1, 'message' => '', 'amount' => $xml['amount']];
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    /**
     * Выписка по счету - список исполненных документов
     * @param array $params
     * @return array
     */
    public function getStatement(array $params)
    {
        $action = '/api/v1/banking/account/statement/document';

        $queryData = [
            'Account' => $params['account'],
            'StartDate' => $params['datefrom'],
            'EndDate' => $params['dateto'],
        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        //$ans['xml'] = '{"Statement":[{"Id":169721172315,"DateDoc":"2019-12-26T00:00:00","Operdate":"2019-12-27T00:00:00","DocNumber":6,"DocSumm":{"Sum":5000.0,"Currency":"RUB"},"DocSummD":-5000.0,"PayerName":"ТКБ БАНК ПАО//9731051046","PayerBik":"044525388","PayerBank":"ТКБ БАНК ПАО","PayerBankAccount":"30101810800000000388","PayerINN":"9731051046","PayerAccount":"30232810100000089118","PayerKPP":"770901001","PayeeName":"ИП Шулькина Елена Андреевна","PayeeBik":"044525092","PayeeBank":"МОСКОВСКИЙ ФИЛИАЛ АО КБ \"МОДУЛЬБАНК\"","PayeeBankAccount":"30101810645250000092","PayeeAccount":"40802810970010219076","PayeeINN":"780423122803","Description":"Вывод средств с виртуального счета. e2bdb4f9-b161-449a-99ba-1e1fb028b4dc","IsCredit":false,"Ro":"01","IsCharge":false,"Queue":"5"},{"Id":169721161191,"DateDoc":"2019-12-26T00:00:00","Operdate":"2019-12-26T00:00:00","DocNumber":6,"DocSumm":{"Sum":45.0,"Currency":"RUB"},"DocSummD":-45.0,"PayerName":"ООО \"ЛЕМОН ОНЛАЙН\"","PayerBik":"044525388","PayerBank":"ТКБ БАНК ПАО","PayerBankAccount":"30101810800000000388","PayerINN":"7709129705","PayerAccount":"30232810100000089118","PayerKPP":"770901001","PayeeName":"ТКБ БАНК ПАО","PayeeBik":"044525388","PayeeBank":"ТКБ БАНК ПАО","PayeeBankAccount":"30101810800000000388","PayeeAccount":"70601810300002740215","PayeeKPP":"770901001","PayeeINN":"7709129705","Description":"Комиссия Банка за проведение операции по документу № 6 от 27.12.2019 года","IsCredit":false,"Ro":"17","IsCharge":false,"Queue":"5"},{"Id":169686144588,"DateDoc":"2019-12-26T00:00:00","Operdate":"2019-12-26T00:00:00","DocNumber":19,"DocSumm":{"Sum":45.0,"Currency":"RUB"},"DocSummD":-45.0,"PayerName":"ООО \"ЛЕМОН ОНЛАЙН\"","PayerBik":"044525388","PayerBank":"ТКБ БАНК ПАО","PayerBankAccount":"30101810800000000388","PayerINN":"7709129705","PayerAccount":"30232810100000089118","PayerKPP":"770901001","PayeeName":"ТКБ БАНК ПАО","PayeeBik":"044525388","PayeeBank":"ТКБ БАНК ПАО","PayeeBankAccount":"30101810800000000388","PayeeAccount":"70601810300002740215","PayeeKPP":"770901001","PayeeINN":"7709129705","Description":"Комиссия Банка за проведение операции по документу № 19 от 26.12.2019 года","IsCredit":false,"Ro":"17","IsCharge":false,"Queue":"5"},{"Id":169648342687,"DateDoc":"2019-12-26T00:00:00","Operdate":"2019-12-26T00:00:00","DocNumber":42,"DocSumm":{"Sum":35000.0,"Currency":"RUB"},"DocSummD":35000.0,"PayerName":"ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ \"ЛЕМОН ОНЛАЙН\"","PayerBik":"046015207","PayerBank":"ФИЛИАЛ \"РОСТОВСКИЙ\" АО \"АЛЬФА-БАНК\"","PayerBankAccount":"30101810500000000207","PayerINN":"9731051046","PayerAccount":"40702810226000005976","PayeeName":"ООО \"ЛЕМОН ОНЛАЙН\"","PayeeBik":"044525388","PayeeBank":"ТКБ БАНК ПАО","PayeeBankAccount":"30101810800000000388","PayeeAccount":"30232810100000089118","PayeeKPP":"770901001","PayeeINN":"9731051046","Description":"Пополнение транзитного счета: 30232810100000089118 по договору  И-0294/19 от 17.12.2019г. Сумма 35000-00 Без налога (НДС)","IsCredit":true,"Ro":"01","IsCharge":false,"Queue":"5"},{"Id":169298935004,"DateDoc":"2019-12-24T00:00:00","Operdate":"2019-12-25T00:00:00","DocNumber":41,"DocSumm":{"Sum":10000.0,"Currency":"RUB"},"DocSummD":10000.0,"PayerName":"ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ \"ЛЕМОН ОНЛАЙН\"","PayerBik":"046015207","PayerBank":"ФИЛИАЛ \"РОСТОВСКИЙ\" АО \"АЛЬФА-БАНК\"","PayerBankAccount":"30101810500000000207","PayerINN":"9731051046","PayerAccount":"40702810226000005976","PayeeName":"ООО \"ЛЕМОН ОНЛАЙН\"","PayeeBik":"044525388","PayeeBank":"ТКБ БАНК ПАО","PayeeBankAccount":"30101810800000000388","PayeeAccount":"30232810100000089118","PayeeKPP":"770901001","PayeeINN":"9731051046","Description":"Пополнение транзитного счета: 30232810100000089118 по договору  И-0294/19 от 17.12.2019г. Сумма 10000-00 Без налога (НДС)","IsCredit":true,"Ro":"01","IsCharge":false,"Queue":"5"}]}';
        //$ans['xml'] = Json::decode($ans['xml']);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $ans['xml'] = self::array_change_key_case_recursive($ans['xml'], CASE_LOWER);
            if (isset($ans['xml']['statement'])) {
                return [
                    'status' => 1,
                    'message' => '',
                    'statements' => $ans['xml']['statement']
                ];
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    /**
     * Выписка по счету - список исполненных документов
     * @param array $params
     * @return array
     */
    public function getStatementNominal(array $params)
    {
        $action = '/nominal/v2/getStatement';

        $queryData = [
            'accountNumber' => $params['account'],
            'startDate' => $params['datefrom'],
            'endDate' => $params['dateto'],
        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrlXml.$action);

        //$ans['xml'] = '';
        //$ans['xml'] = Json::decode($ans['xml']);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $ans['xml'] = self::array_change_key_case_recursive($ans['xml'], CASE_LOWER);
            if (isset($ans['xml']['documents'])) {
                return [
                    'status' => 1,
                    'message' => '',
                    'statements' => $ans['xml']['documents']
                ];
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    /**
     * Выписка по счету (ABS) (для номинального счета)
     * @param array $params
     * @return array
     */
    public function getStatementAbs(array $params)
    {
        $action = '/api/v1/getstatementABS';

        $queryData = [
            'AccountNumber' => $params['account'],
            'DateStart' => $params['datefrom'],
            'DateEnd' => $params['dateto'],
        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        //$ans['xml'] = '{"Transactions":[{"Account":"40701810820020100001","AccountCredit":"40701810820020100001","AccountDebet":"30102810900000000388","BranchID":0,"CardAmount":200000.0,"CardCurrency":"XTS","CardDeviceType":0,"CardID":0,"CardNetID":0,"Comment":"Пополнение лицевого счета МСБ ОНЛАЙН на электронной платформе \"ЛЕМОН ОНЛАЙН\" Сумма 200000-00 Без налога (НДС)","ExecDate":"2019-12-25T00:00:00","FeeAmount":0.0,"FeeMerchBankAmount":0.0,"Hold":false,"ID":169382686642,"IsCash":false,"IsFee":false,"IsInet":false,"IsOnline":false,"NOper":0.0,"OperDate":"2019-12-25T00:00:00","PayCurrency":"RUB","PriRas":"C","TotalAmount":200000.0,"TransactID":0,"ValNetAmount":200000.0,"ValNetCurrency":"RUB"},{"Account":"40701810820020100001","AccountCredit":"40701810820020100001","AccountDebet":"30102810900000000388","BranchID":0,"CardAmount":5000.0,"CardCurrency":"XTS","CardDeviceType":0,"CardID":0,"CardNetID":0,"Comment":"Пополнение лицевого счета владельца ИНН 121524898903 на электронной платформе \"ЛЕМОН ОНЛАЙН\" Сумма Без налога (НДС)","ExecDate":"2019-12-24T00:00:00","FeeAmount":0.0,"FeeMerchBankAmount":0.0,"Hold":false,"ID":169067401209,"IsCash":false,"IsFee":false,"IsInet":false,"IsOnline":false,"NOper":0.0,"OperDate":"2019-12-24T00:00:00","PayCurrency":"RUB","PriRas":"C","TotalAmount":5000.0,"TransactID":0,"ValNetAmount":5000.0,"ValNetCurrency":"RUB"},{"Account":"40701810820020100001","AccountCredit":"40701810820020100001","AccountDebet":"30102810900000000388","BranchID":0,"CardAmount":5000.0,"CardCurrency":"XTS","CardDeviceType":0,"CardID":0,"CardNetID":0,"Comment":"Пополнение лицевого счета владельца ИНН 332400408444 на электронной платформе \"ЛЕМОН ОНЛАЙН\" Сумма Без налога (НДС) Без НДС","ExecDate":"2019-12-24T00:00:00","FeeAmount":0.0,"FeeMerchBankAmount":0.0,"Hold":false,"ID":169071053989,"IsCash":false,"IsFee":false,"IsInet":false,"IsOnline":false,"NOper":0.0,"OperDate":"2019-12-24T00:00:00","PayCurrency":"RUB","PriRas":"C","TotalAmount":5000.0,"TransactID":0,"ValNetAmount":5000.0,"ValNetCurrency":"RUB"},{"Account":"40701810820020100001","AccountCredit":"40701810820020100001","AccountDebet":"30102810900000000388","BranchID":0,"CardAmount":182000.0,"CardCurrency":"XTS","CardDeviceType":0,"CardID":0,"CardNetID":0,"Comment":"Пополнение лицевого счета МСБ ОНЛАЙН на электронной платформе \"ЛЕМОН ОНЛАЙН\" Сумма 182000-00 Без налога (НДС)","ExecDate":"2019-12-24T00:00:00","FeeAmount":0.0,"FeeMerchBankAmount":0.0,"Hold":false,"ID":169082413799,"IsCash":false,"IsFee":false,"IsInet":false,"IsOnline":false,"NOper":0.0,"OperDate":"2019-12-24T00:00:00","PayCurrency":"RUB","PriRas":"C","TotalAmount":182000.0,"TransactID":0,"ValNetAmount":182000.0,"ValNetCurrency":"RUB"},{"Account":"40701810820020100001","AccountCredit":"40701810820020100001","AccountDebet":"30102810900000000388","BranchID":0,"CardAmount":10000.0,"CardCurrency":"XTS","CardDeviceType":0,"CardID":0,"CardNetID":0,"Comment":"Пополнение лицевого счета владельца ИНН 744408469746 на электронной платформе \"ЛЕМОН ОНЛАЙН\" Сумма Без налога (НДС)","ExecDate":"2019-12-24T00:00:00","FeeAmount":0.0,"FeeMerchBankAmount":0.0,"Hold":false,"ID":169114032397,"IsCash":false,"IsFee":false,"IsInet":false,"IsOnline":false,"NOper":0.0,"OperDate":"2019-12-24T00:00:00","PayCurrency":"RUB","PriRas":"C","TotalAmount":10000.0,"TransactID":0,"ValNetAmount":10000.0,"ValNetCurrency":"RUB"},{"Account":"40701810820020100001","AccountCredit":"40701810820020100001","AccountDebet":"30102810900000000388","BranchID":0,"CardAmount":50000.0,"CardCurrency":"XTS","CardDeviceType":0,"CardID":0,"CardNetID":0,"Comment":"Пополнение лицевого счета владельца ИНН 502771963554 на электронной платформе \"ЛЕМОН ОНЛАЙН\" Сумма Без налога (НДС)","ExecDate":"2019-12-24T00:00:00","FeeAmount":0.0,"FeeMerchBankAmount":0.0,"Hold":false,"ID":169153340417,"IsCash":false,"IsFee":false,"IsInet":false,"IsOnline":false,"NOper":0.0,"OperDate":"2019-12-24T00:00:00","PayCurrency":"RUB","PriRas":"C","TotalAmount":50000.0,"TransactID":0,"ValNetAmount":50000.0,"ValNetCurrency":"RUB"}]}';
        //$ans['xml'] = Json::decode($ans['xml']);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $ans['xml'] = self::array_change_key_case_recursive($ans['xml'], CASE_LOWER);
            if (isset($ans['xml']['transactions'])) {
                return [
                    'status' => 1,
                    'message' => '',
                    'statements' => $ans['xml']['transactions']
                ];
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    public function ActivateCard($Id, array $params)
    {
        $action = '/api/tcbpay/gate/activatecard';
        $queryData = [
            "OrderID" => $Id,
            "EAN" => $params["cardnum"],
            "ClientData" => [
                "Sex" => $params["client"]["sex"],
                "FirstName" => $params["client"]["firstname"],
                "MiddleName" => $params["client"]["middlename"],
                "FamilyName" => $params["client"]["surname"],
                "MobilePhone" => $params["client"]["phone"],
                "Birth" => [
                    "Day" => $params["client"]["birthday"],
                    "Place" => $params["client"]["birthplace"],
                    "Country" => [
                        "Code" => $params["client"]["birthcountrycode"],
                        "Name" => $params["client"]["birthcountry"]
                    ],
                ],
                "Country" => [
                    "Code" => $params["client"]["countrycode"],
                    "Name" => $params["client"]["countryname"]
                ],
                "City" => [
                    "Code" => $params["client"]["citycode"],
                    "Name" => $params["client"]["cityname"]
                ],
                "RegistrationAddress" => [
                    "Country" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["country"]
                    ],
                    "Region" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["region"]
                    ],
                    "District" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["district"]
                    ],
                    "City" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["city"]
                    ],
                    "Settlement" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["settlement"]
                    ],
                    "Street" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["street"]
                    ],
                    "House" => $params["client"]["registrationaddress"]["house"],
                    "Flat" => $params["client"]["registrationaddress"]["flat"]
                ],
                "PostalAddress" => [
                    "Country" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["country"]
                    ],
                    "Region" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["region"]
                    ],
                    "District" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["district"]
                    ],
                    "City" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["city"]
                    ],
                    "Settlement" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["settlement"]
                    ],
                    "Street" => [
                        "Code" => "",
                        "Name" => $params["client"]["registrationaddress"]["street"]
                    ],
                    "House" => $params["client"]["registrationaddress"]["house"],
                    "Flat" => $params["client"]["registrationaddress"]["flat"]
                ],
                "Document" => [
                    "Num" => $params["client"]["document"]["num"],
                    "Series" => $params["client"]["document"]["series"],
                    "Date" => $params["client"]["document"]["date"],
                    "RegName" => $params["client"]["document"]["regname"],
                    "RegCode" => $params["client"]["document"]["regcode"],
                    "DateEnd" => $params["client"]["document"]["dateend"]
                ]
            ],
            "ControlInfo" => $params["controlword"],

        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['Status']) && $xml['Status'] == '0') {
                return ['status' => 1, 'message' => ''];
            }
        }

        return ['status' => 0, 'message' => ''];

    }

    public function SimpleActivateCard($Id, array $params)
    {
        $action = '/api/tcbpay/gate/simpleactivatecard';
        $queryData = [
            "OrderID" => $Id,
            "EAN" => $params["cardnum"],
            "ClientData" => [
                "Sex" => $params["client"]["sex"],
                "FirstName" => $params["client"]["firstname"],
                "MiddleName" => $params["client"]["middlename"],
                "FamilyName" => $params["client"]["surname"],
                "MobilePhone" => $params["client"]["phone"],
                "Country" => [
                    "Code" => $params["client"]["countrycode"],
                    "Name" => $params["client"]["countryname"]
                ],
                "City" => [
                    "Code" => $params["client"]["citycode"],
                    "Name" => $params["client"]["cityname"]
                ],
                "Document" => [
                    "Num" => $params["client"]["document"]["num"],
                    "Series" => $params["client"]["document"]["series"]
                ]
            ],
            "ControlInfo" => $params["controlword"],

        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['Status']) && $xml['Status'] == '0') {
                return ['status' => 1, 'message' => ''];
            }
        }

        return ['status' => 0, 'message' => ''];
    }

    public function StateActivateCard($Id)
    {
        $action = '/api/tcbpay/gate/getactivatecardstate';
        $queryData = [
            "OrderID" => $Id
        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['Status'])) {
                $state = 0;
                if ($xml['Status'] == 3) {
                    $state = 1;
                } elseif ($xml['Status'] == 6) {
                    $state = 2;
                }
                return ['status' => $state, 'message' => $xml['StateDescription']];
            }
        }

        return ['status' => 0, 'message' => ''];
    }

    /**
     * Проверка карты по бин
     * @param $CardNum
     * @return array
     */
    public function GetBinDBInfo($CardNum)
    {
        $CardNum = str_ireplace(' ', '', $CardNum);
        $action = '/api/tcbpay/gate/getbindbinfo';
        $queryData = [
            "BIN" => substr($CardNum, 7, 1) == "*" ? substr($CardNum, 0, 6) : substr($CardNum, 0, 8)
        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['bininfo'])) {
                return ['status' => 1, 'info' => $xml['bininfo']];
            }
        }

        return ['status' => 0, 'message' => ''];

    }

    /**
     * Оплата без формы (PCI DSS)
     * @param array $params
     * @return array
     */
    public function PayXml(array $params)
    {
        $action = '/api/tcbpay/gate/registerorderfromunregisteredcardwof';

        $queryData = [
            'OrderID' => $params['ID'],
            'Amount' => $params['SummFull'],
            'Description' => 'Оплата по счету ' . $params['ID'],
            'CardInfo' => [
                'CardNumber' => $params['card']['number'],
                'CardHolder' => $params['card']['holder'],
                'ExpirationYear' => intval("20" . $params['card']['year']),
                'ExpirationMonth' => intval($params['card']['month']),
                'CVV' => $params['card']['cvc'],
            ],
            //'ReturnUrl' => $this->backUrls['ok'].$params['ID'],
            'ShowReturnButton' => false,
            'TTL' => '00.00:' . ($params['TimeElapsed'] / 60) . ':00'
        ];

        if (!empty($params['Email'])) {
            $queryData['ClientInfo']['Email'] = $params['Email'];
        }

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['Status']) && $xml['Status'] == '0') {
                return ['status' => 1,
                    'transac' => $xml['ordernumber'],
                    'url' => $xml['acsurl'],
                    'pa' => $xml['pareq'],
                    'md' => $xml['md']
                ];
            } else {
                return ['status' => 2, 'message' => $xml['errorinfo']['errormessage']];
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];

    }

    /**
     * Оплата ApplePay
     * @param array $params
     * @return array
     */
    public function PayApple(array $params)
    {
        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    /**
     * Оплата GooglePay
     * @param array $params
     * @return array
     */
    public function PayGoogle(array $params)
    {
        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    /**
     * Оплата SamsungPay
     * @param array $params
     * @return array
     */
    public function PaySamsung(array $params)
    {
        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    /**
     * Финиш оплаты без формы (PCI DSS)
     * @param array $params
     * @return array
     */
    public function ConfirmXml(array $params)
    {
        $action = '/api/tcbpay/gate/registerorderfromcardfinish';

        $queryData = [
            'OrderID' => $params['ID'],
            'MD' => $params['MD'],
            'PaRes' => $params['PaRes'],
        ];

        $queryData = Json::encode($queryData);

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['Status']) && $xml['Status'] == '0') {
                return ['status' => 1, 'transac' => $xml['ordernumber']];
            } else {
                return ['status' => 2, 'message' => $xml['errorinfo']['errormessage']];
            }
        }

        return ['status' => 0, 'message' => '', 'fatal' => 0];

    }

    /**
     * Регистрация бенифициаров
     *
     * @param array $params
     * @return array
     */
    public function RegisterBenificiar(array $params)
    {
        $action = '/cxf/CftNominalService';

        $queryData = $params['req'];

        $ans = $this->curlXmlReq($queryData,
            $this->bankUrlXml.$action,
            ['SOAPAction: "http://cft.transcapital.ru/CftNominalIntegrator/SetBeneficiary"'],
            false
        );

        //$ans['xml'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><ns2:SetBeneficiaryResponse xmlns:ns2="http://cft.transcapital.ru/CftNominalIntegrator/"><errCode>0</errCode><errMsg></errMsg></ns2:SetBeneficiaryResponse></soap:Body></soap:Envelope>';

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            return [
                'status' => 1,
                'message' => '',
                'soap' => $ans['xml']
            ];
        } elseif (isset($ans['httperror']) && !empty($ans['httperror'])) {
            return [
                'status' => 1,
                'message' => '',
                'soap' => $ans['httperror']
            ];
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    private function GetCardType($strbrand)
    {
        //0 - visa, 1 - mastercard 2 - mir 3 - american express 4 - JCB 5 - Dinnersclub
        if (strtoupper($strbrand) === 'VISA') {
            return 0;
        } elseif (strtoupper($strbrand) === 'MASTER') {
            return 1;
        } elseif (strtoupper($strbrand) === 'MIR') {
            return 2;
        } elseif (strtoupper($strbrand) === 'AMERICANEXPRESS') {
            return 3;
        } elseif (strtoupper($strbrand) === 'JCB') {
            return 4;
        } elseif (strtoupper($strbrand) === 'DINNERS') {
            return 5;
        }

        return 0;
    }

    private function buildSoapRequestRawBody($method, $data)
    {
        $xml = new SimpleXMLElement('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:p2p="http://engine.paymentgate.ru/webservices/p2p" />');
    }

    /**
     * @param CreatePayForm $createPayForm
     * @return CreatePayResponse
     * @throws BankAdapterResponseException
     * @throws Check3DSv2Exception
     * @throws CreatePayException
     */
    public function createPay(CreatePayForm $createPayForm)
    {
        /** @var Check3DSVersionResponse $check3DSVersionResponse */
        $check3DSVersionResponse = $this->check3DSVersion($createPayForm);

        if(in_array($check3DSVersionResponse->version, Issuer3DSVersionInterface::V_2)) {
            // TODO: add strategy 3ds v2
            $payResponse = new CreatePayResponse();
            $payResponse->vesion3DS = $check3DSVersionResponse->version;
            $payResponse->status = BaseResponse::STATUS_CREATED;
            $payResponse->isNeedSendTransIdTKB = true;
            $payResponse->threeDSServerTransID = $check3DSVersionResponse->threeDSServerTransID;
            $payResponse->threeDSMethodURL = $check3DSVersionResponse->threeDSMethodURL;
            $payResponse->cardRefId = $check3DSVersionResponse->cardRefId;
            return $payResponse;
        } else {
            $payResponse = $this->createPay3DSv1($createPayForm, $check3DSVersionResponse);
        }

        $payResponse->isNeed3DSRedirect = false;
        return $payResponse;
    }

    /**
     * @param CreatePaySecondStepForm $createPaySecondStepForm
     * @return CreatePayResponse
     * @throws Check3DSv2Exception
     * @throws CreatePayException
     */
    public function createPayStep2(CreatePaySecondStepForm $createPaySecondStepForm)
    {
        $checkDataCacheKey = Cache3DSv2Interface::CACHE_PREFIX_CHECK_DATA . $createPaySecondStepForm->getPaySchet()->ID;

        if(Yii::$app->cache->exists($checkDataCacheKey)) {
            $checkData = Yii::$app->cache->get($checkDataCacheKey);

            $check3DSVersionResponse = new Check3DSVersionResponse();
            $check3DSVersionResponse->cardRefId = ($checkData['cardRefId'] ?? '');
            $check3DSVersionResponse->transactionId = ($checkData['transactionId'] ?? '');

            $paySchet = $createPaySecondStepForm->getPaySchet();
            $payResponse = $this->createPay3DSv2($paySchet, $check3DSVersionResponse);

            $payResponse->isNeed3DSRedirect = false;
            return $payResponse;
        }
    }

    /**
     * @param $createPayForm
     * @param $check3DSVersionResponse
     * @return CreatePayResponse
     * @throws BankAdapterResponseException
     * @throws MerchantRequestAlreadyExistsException
     */
    protected function createPay3DSv1($createPayForm, $check3DSVersionResponse)
    {
        $action = '/api/tcbpay/gate/registerorderfromunregisteredcardwof';

        $paySchet = $createPayForm->getPaySchet();
        $createPayRequest = new CreatePayRequest();
        $createPayRequest->OrderId = $paySchet->ID;
        $createPayRequest->Amount = $paySchet->getSummFull();
        $createPayRequest->Description = 'Оплата по счету ' . $paySchet->ID;
        $createPayRequest->TTL = '00.00:' . ($paySchet->TimeElapsed / 60) . ':00';

        $createPayRequest->CardInfo = [
            'CardNumber' => $createPayForm->CardNumber,
            'CardHolder' => $createPayForm->CardHolder,
            'ExpirationYear' => intval("20" . $createPayForm->CardYear),
            'ExpirationMonth' => intval($createPayForm->CardMonth),
            'CVV' => $createPayForm->CardCVC,
        ];

        if(!empty($paySchet->UserEmail)) {
            $createPayRequest->ClientInfo = [
                'Email' => $paySchet->UserEmail,
            ] ;
        }
        $queryData = Json::encode($createPayRequest->getAttributes());

        // TODO: response as object
        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);
        if (isset($ans['xml']['errorinfo']['errorcode']) && $ans['xml']['errorinfo']['errorcode'] === 1 && $ans['xml']['ordernumber'] == 0) {
            //Не уверен что это правильно, если банк введёт локализацию, то работать не будет.
            if (strpos($ans['xml']['errorinfo']['errormessage'], 'уже существует') !== false){
                throw new MerchantRequestAlreadyExistsException('Ошибка запроса, попробуйте повторить позднее');
            }
        }

        $payResponse = new CreatePayResponse();
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['Status']) && $xml['Status'] == '0') {
                $payResponse->status = 1;
                $payResponse->transac = $xml['ordernumber'];
                $payResponse->url = $xml['acsurl'];
                $payResponse->pa = $xml['pareq'];
                $payResponse->md = $xml['md'];

            } else {
                $payResponse->status = BaseResponse::STATUS_ERROR;
                $payResponse->message = $xml['errorinfo']['errormessage'];
            }
        } else {
            throw new BankAdapterResponseException('Ошибка запроса, попробуйте повторить позднее');
        }

        return $payResponse;
    }

    /**
     * @param DonePayForm $donePayForm
     * @return $confirmPayResponse
     */
    public function confirm(DonePayForm $donePayForm)
    {
        $paySchet = $donePayForm->getPaySchet();

        $checkDataCacheKey = Cache3DSv2Interface::CACHE_PREFIX_CHECK_DATA . $paySchet->ID;
        if(Yii::$app->cache->exists($checkDataCacheKey)
            && in_array(Yii::$app->cache->get($checkDataCacheKey)['version'], Issuer3DSVersionInterface::V_2)
        ) {
            return $this->confirmBy3DSv2($donePayForm);
        } else {
            return $this->confirmBy3DSv1($donePayForm);
        }
    }

    protected function confirmBy3DSv1(DonePayForm $donePayForm)
    {
        $action = '/api/tcbpay/gate/registerorderfromcardfinish';

        $paySchet = $donePayForm->getPaySchet();
        $donePayRequest = new DonePayRequest();
        $donePayRequest->OrderId = $donePayForm->IdPay;
        $donePayRequest->MD = $donePayForm->md;
        $donePayRequest->PaRes = $donePayForm->paRes;

        $queryData = Json::encode($donePayRequest->getAttributes());

        $confirmPayResponse = new ConfirmPayResponse();

        // TODO: response as object
        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['Status']) && $xml['Status'] == '0') {
                $confirmPayResponse->status = BaseResponse::STATUS_DONE;
                $confirmPayResponse->message = 'OK';
                $confirmPayResponse->transac = $xml['ordernumber'];

                $paySchet->ExtBillNumber = $confirmPayResponse->transac;
                $paySchet->save(false);
            } else {
                $confirmPayResponse->status = BaseResponse::STATUS_ERROR;
                $confirmPayResponse->message = $xml['errorinfo']['errormessage'];
            }
        } else {
            throw new BankAdapterResponseException('Ошибка запроса, попробуйте повторить позднее');
        }

        if(!$confirmPayResponse->validate()) {
            throw new BankAdapterResponseException('Ошибка формата ответа');
        }

        return $confirmPayResponse;
    }

    /**
     * @param DonePayForm $donePayForm
     * @return ConfirmPayResponse
     * @throws BankAdapterResponseException
     * @throws CreatePayException
     */
    protected function confirmBy3DSv2(DonePayForm $donePayForm)
    {
        $paySchet = $donePayForm->getPaySchet();

        if($paySchet->IsNeed3DSVerif) {
            Yii::warning('TKBankAdapter confirmBy3DSv2: ID=' . $paySchet->ID
                . ' IsNeed3DSVerif=' . $paySchet->IsNeed3DSVerif
            );
            $this->validateBy3DSv2($donePayForm);
        }
        return $this->finishBy3DSv2($donePayForm);
    }


    /**
     * @param DonePayForm $donePayForm
     * @return bool
     * @throws BankAdapterResponseException
     */
    protected function validateBy3DSv2(DonePayForm $donePayForm)
    {
        $action = '/api/v1/card/unregistered/debit/3ds2Validate';

        $cardRefId = Yii::$app->cache->get(Cache3DSv2Interface::CACHE_PREFIX_CARD_REF_ID . $donePayForm->getPaySchet()->ID);
        $confirm3DSv2Request = new Confirm3DSv2Request();
        $confirm3DSv2Request->ExtID = $donePayForm->getPaySchet()->ID;
        $confirm3DSv2Request->Amount = $donePayForm->getPaySchet()->getSummFull();
        $confirm3DSv2Request->Cres = $donePayForm->cres ?? Yii::$app->cache->get(Cache3DSv2Interface::CACHE_PREFIX_CRES);
        // TODO: refact on tokenize
        $confirm3DSv2Request->CardInfo = [
            'CardRefId' => $cardRefId,
        ];

        Yii::warning('TKBankAdapter get cardRefId cache: paySchet.ID=' . $donePayForm->getPaySchet()->ID
            . ' paySchet.Extid=' . $donePayForm->getPaySchet()->Extid
            . ' cardRefId=' . $cardRefId
        );
        Yii::warning('TKBankAdapter get paySchet: paySchet.ID=' . $donePayForm->getPaySchet()->ID
            . ' cardRefId=' . $donePayForm->getPaySchet()->CardRefId3DS
        );

        $queryData = Json::encode($confirm3DSv2Request->getAttributes());
        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if (isset($ans['xml']['AuthenticationData']) && !empty($ans['xml']['AuthenticationData'])) {
            Yii::$app->cache->set(
                Cache3DSv2Interface::CACHE_PREFIX_AUTH_DATA . $donePayForm->getPaySchet()->ID,
                json_encode($ans['xml']['AuthenticationData']),
                3600
            );
            return true;
        } elseif (isset($ans['xml']['authenticationData']) && !empty($ans['xml']['authenticationData'])) {
            Yii::$app->cache->set(
                Cache3DSv2Interface::CACHE_PREFIX_AUTH_DATA . $donePayForm->getPaySchet()->ID,
                json_encode($ans['xml']['authenticationData']),
                3600
            );
            return true;
        } else {
            throw new BankAdapterResponseException('Ошибка запроса, попробуйте повторить позднее');
        }
    }


    protected function finishBy3DSv2(DonePayForm $donePayForm)
    {
        $action = '/api/v1/card/unregistered/debit/3ds2/wof/finish';

        $paySchet = $donePayForm->getPaySchet();
        $donePay3DSv2Request = new DonePay3DSv2Request();
        $donePay3DSv2Request->ExtId = $paySchet->ID;
        $donePay3DSv2Request->Amount = $paySchet->getSummFull();
        $donePay3DSv2Request->Description = 'Оплата по счету ' . $paySchet->ID;
        $donePay3DSv2Request->CardInfo = [
            'CardRefId' => $paySchet->CardRefId3DS,
        ];

        $donePay3DSv2Request->AuthenticationData = json_decode(Yii::$app->cache->get('PaySchet_3DSv2_AuthData_' . $paySchet->ID), true);
        $confirmPayResponse = new ConfirmPayResponse();

        $queryData = Json::encode($donePay3DSv2Request->getAttributes());
        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if(!isset($ans['xml']['OrderId'])) {
            throw new CreatePayException('Ошибка подтверждения платежа 3DS v2');
        }

        $paySchet->ExtBillNumber = $ans['xml']['OrderId'];
        $paySchet->save(false);

        $confirmPayResponse->status = BaseResponse::STATUS_DONE;
        $confirmPayResponse->message = 'Успешно';
        $confirmPayResponse->transac = $ans['xml']['OrderId'];

        return $confirmPayResponse;
    }

    /**
     * @param OkPayForm $okPayForm
     * @return CheckStatusPayResponse|mixed
     * @throws BankAdapterResponseException
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        $action = '/api/tcbpay/gate/getorderstate';

        $checkStatusPayRequest = new CheckStatusPayRequest();
        $checkStatusPayRequest->OrderID = $okPayForm->IdPay;

        $queryData = Json::encode($checkStatusPayRequest->getAttributes());
        $response = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        $checkStatusPayResponse = new CheckStatusPayResponse();
        Yii::warning("checkStatusOrder: " . $this->logArr($response), 'merchant');
        if (isset($response['xml']) && !empty($response['xml'])) {
            $xml = $this->parseAns($response['xml']);
            Yii::warning("checkStatusOrder afterParseAns: " . Json::encode($xml), 'merchant');
            if ($xml && isset($xml['errorinfo']['errorcode']) && (int)$xml['errorinfo']['errorcode'] > 0) {
                $errorCode = (int)$xml['errorinfo']['errorcode'];
                Yii::warning("checkStatusPay isCreated IdPay=" . $okPayForm->IdPay, 'merchant');
                if($errorCode == ITKBankAdapterResponseErrors::ERROR_CODE_ENGINEERING_WORKS) {
                    $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
                } else {
                    $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                }

                $checkStatusPayResponse->message = $xml['errorinfo']['errormessage'];
                $checkStatusPayResponse->xml = $xml;
            } else {
                $status = $this->convertState($xml);
                Yii::warning("checkStatusPay afterConvertStatus IdPay=" . $okPayForm->IdPay . " : " . $status, 'merchant');
                if(!in_array($status, BaseResponse::STATUSES)) {
                    throw new BankAdapterResponseException('Ошибка преобразования статусов');
                }

                if($status == BaseResponse::STATUS_DONE && isset($xml['orderinfo']['orderid'])) {
                    $checkStatusPayResponse->transId = $xml['orderinfo']['orderid'];
                }

                $checkStatusPayResponse->status = $status;
                $checkStatusPayResponse->xml = $xml;
                $checkStatusPayResponse->rrn = $xml['orderadditionalinfo']['rrn'] ?? '';
                $checkStatusPayResponse->cardRefId = $xml['orderadditionalinfo']['data']['cardrefid'] ?? '';
                $checkStatusPayResponse->message = $xml['orderinfo']['statedescription'] ?? '';
                $checkStatusPayResponse->expYear = $xml['orderadditionalinfo']['cardexpyear'] ?? '';
                $checkStatusPayResponse->expMonth = $xml['orderadditionalinfo']['cardexpmonth'] ?? '';
                $checkStatusPayResponse->cardHolder = $xml['orderadditionalinfo']['cardholder'] ?? '';
                $checkStatusPayResponse->cardNumber = $xml['orderadditionalinfo']['cardnumber'] ?? '';
            }
        } else {
            throw new BankAdapterResponseException('Ошибка запроса, попробуйте повторить позднее');
        }

        return $checkStatusPayResponse;
    }

    /**
     * @param AutoPayForm $autoPayForm
     * @return CreateRecurrentPayResponse
     */
    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        $action = '/api/tcbpay/gate/registerdirectorderfromregisteredcard';

        $createRecurrentPayRequest = new CreateRecurrentPayRequest();
        $createRecurrentPayRequest->OrderId = $autoPayForm->paySchet->ID;
        $createRecurrentPayRequest->Amount = $autoPayForm->paySchet->getSummFull();
        $createRecurrentPayRequest->Description = 'Оплата по счету ' . $autoPayForm->paySchet->ID;

        $card = $autoPayForm->getCard();
        $createRecurrentPayRequest->CardRefID = $card->ExtCardIDP;

        $queryData = Json::encode($createRecurrentPayRequest->getAttributes());
        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        $createRecurrentPayResponse = new CreateRecurrentPayResponse();
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $this->parseAns($ans['xml']);
            if (isset($xml['ordernumber'])) {
                $createRecurrentPayResponse->status = BaseResponse::STATUS_DONE;
                $createRecurrentPayResponse->transac = $xml['ordernumber'];
                return $createRecurrentPayResponse;
            }
        }

        $createRecurrentPayResponse->status = BaseResponse::STATUS_ERROR;
        $createRecurrentPayResponse->message = '';
        return $createRecurrentPayResponse;
    }


    public function refundPay(RefundPayForm $refundPayForm)
    {
        $refundPayResponse = new RefundPayResponse();

        $paySchet = $refundPayForm->paySchet;
        if($paySchet->Status != PaySchet::STATUS_DONE) {
            throw new RefundPayException('Невозможно отменить незавершенный платеж');
        }

        $refundPayRequest = new RefundPayRequest();
        $refundPayRequest->ExtId = $paySchet->ID;

        $action = '/api/v1/card/unregistered/debit/reverse';
        if($paySchet->DateCreate < Carbon::now()->startOfDay()->timestamp) {
            $refundPayRequest->amount = $paySchet->getSummFull();
            $action = '/api/v1/card/unregistered/debit/refund';
        }

        $ans = $this->curlXmlReq(Json::encode($refundPayRequest->getAttributes()), $this->bankUrl . $action);
        Yii::warning("reversOrder: " . $this->logArr($ans), 'merchant');
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $status = isset($ans['xml']['errorinfo']['errorcode']) ? $ans['xml']['errorinfo']['errorcode'] : 1;
            $message = isset($ans['xml']['errorinfo']['errormessage']) ? $ans['xml']['errorinfo']['errormessage'] : 'Ошибка запроса';

            $refundPayResponse->state = $status == 0;
            $refundPayResponse->status = $status;
            $refundPayResponse->message = $message;

            return $refundPayResponse;
        }
    }

    /**
     * @inheritDoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        $action = '/api/tcbpay/gate/registerordertounregisteredcard';

        $outCardPayRequest = new OutCardPayRequest();
        $outCardPayRequest->OrderId = $outCardPayForm->paySchet->ID;
        $outCardPayRequest->Amount = $outCardPayForm->paySchet->getSummFull();
        $outCardPayRequest->CardInfo = [
            'CardNumber' => $outCardPayForm->cardnum,
        ];

        $ans = $this->curlXmlReq(Json::encode($outCardPayRequest->getAttributes()), $this->bankUrl . $action);

        $outCardPayResponse = new OutCardPayResponse();

        if(isset($ans['xml'])) {
            if(isset($ans['xml']['errorinfo']['errorcode']) && $ans['xml']['errorinfo']['errorcode'] == 0) {
                $outCardPayResponse->status = BaseResponse::STATUS_DONE;
                $outCardPayResponse->trans = $ans['xml']['ordernumber'];
                $outCardPayResponse->message = $ans['xml']['errorinfo']['errormessage'];
            } else {
                $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
                $outCardPayResponse->message = $ans['xml']['errorinfo']['errormessage'];
            }
        } else {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = 'Ошибка запроса';
        }

        return $outCardPayResponse;
    }

    public function getAftMinSum()
    {
        return self::AFT_MIN_SUMM;
    }

    /**
     * @param GetBalanceRequest $getBalanceRequest
     * @return GetBalanceResponse
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest): GetBalanceResponse
    {
        $request = [];
        $getBalanceResponse = new GetBalanceResponse();
        if (empty($getBalanceRequest->accountNumber)) {
            return $getBalanceResponse;
        }
        $getBalanceResponse->bank_name = $getBalanceRequest->bankName;

        $type = $getBalanceRequest->accountType;
        $request['account'] = $getBalanceRequest->accountNumber;
        $response = $this->getBalanceAcc($request);
        if (!isset($response['amount']) && $response['status'] === 0) {
            Yii::warning("Balance service:: TKB request failed for type: $type message: " . $response['message']);
        }
        $getBalanceResponse->amount = (float)$response['amount'];
        $getBalanceResponse->currency = 'RUB';
        $getBalanceResponse->account_type = $type;
        return $getBalanceResponse;
    }

    /**
     * @inheritDoc
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        $action = '/api/tcbpay/gate/registerordertoexternalaccount';

        $outAccountPayRequest = new TransferToAccountRequest();
        $outAccountPayRequest->Inn = $outPayaccForm->inn;
        $outAccountPayRequest->OrderId = (string)$outPayaccForm->paySchet->ID;
        $outAccountPayRequest->Name = ($outPayaccForm->scenario == OutPayAccountForm::SCENARIO_FL ? $outPayaccForm->fio : $outPayaccForm->name);
        $outAccountPayRequest->Bik = strval($outPayaccForm->bic);
        $outAccountPayRequest->Account = strval($outPayaccForm->account);
        $outAccountPayRequest->Amount = $outPayaccForm->amount;
        $outAccountPayRequest->Description = $outPayaccForm->descript;

        $ans = $this->curlXmlReq(Json::encode($outAccountPayRequest->getAttributes()), $this->bankUrl . $action);

        $outAccountPayResponse = new TransferToAccountResponse();
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if(isset($ans['xml']['errorinfo']['errorcode']) && $ans['xml']['errorinfo']['errorcode'] == 0) {
                $outAccountPayResponse->status = BaseResponse::STATUS_DONE;
                $outAccountPayResponse->trans = $ans['xml']['ordernumber'];
            } elseif (isset($ans['xml']['errorinfo']['errorcode'])) {
                $outAccountPayResponse->status = BaseResponse::STATUS_ERROR;
                $outAccountPayResponse->message = $ans['xml']['errorinfo']['errormessage'];
            } else {
                $outAccountPayResponse->status = BaseResponse::STATUS_ERROR;
                $outAccountPayResponse->message = 'Ошибка запроса';
            }
        } else {
            $outAccountPayResponse->status = BaseResponse::STATUS_ERROR;
            $outAccountPayResponse->message = 'Ошибка запроса';
        }

        return $outAccountPayResponse;
    }

    public function identInit(Ident $ident)
    {
        $action = "/api/government/identification/simplifiedpersonidentification";
        $queryData = [];

        foreach (Ident::getTkbRequestParams() as $key => $attributeName) {
            if(!empty($ident->$attributeName)) {
                $queryData[$key] = $ident->$attributeName;
            }
        }

        $identResponse = new IdentInitResponse();
        if(Yii::$app->params['TESTMODE'] == 'Y') {
            $identResponse->status = BaseResponse::STATUS_DONE;
            $identResponse->response = [];
            return $identResponse;
        }

        $ans = $this->curlXmlReq(Json::encode($queryData), $this->bankUrl . $action);

        if (isset($ans['xml']) && isset($ans['xml']['OrderId']) && !empty($ans['xml']['OrderId'])) {
            $identResponse->status = BaseResponse::STATUS_DONE;
        } else {
            $identResponse->status = BaseResponse::STATUS_ERROR;
        }

        return $identResponse;
    }

    /**
     * @inheritDoc
     */
    public function identGetStatus(Ident $ident)
    {
        $action = "/api/government/identification/simplifiedpersonidentificationresult";
        $queryData = [
            'ExtId' => $ident->Id,
        ];
        $queryData = Json::encode($queryData);
        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        $identGetStatusResponse = new IdentGetStatusResponse();
        if(Yii::$app->params['TESTMODE'] == 'Y') {
            $identGetStatusResponse->status = BaseResponse::STATUS_DONE;
            $identGetStatusResponse->identStatus = Ident::STATUS_SUCCESS;
            $identGetStatusResponse->response = ['message' => 'На тестовой среде идентифиткация всегда успешна'];
            return $identGetStatusResponse;
        }

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $identStatus = $this->convertIdentGetStatus($ident, $ans['xml']);
            $identGetStatusResponse->status = ($identStatus == Ident::STATUS_WAITING ? BaseResponse::STATUS_CREATED : BaseResponse::STATUS_DONE);
            $identGetStatusResponse->identStatus = $this->convertIdentGetStatus($ident, $ans['xml']);
            $identGetStatusResponse->response = $ans['xml'];
        } else {
            $identGetStatusResponse->status = BaseResponse::STATUS_ERROR;
            $identGetStatusResponse->response = $ans;
        }

        return $identGetStatusResponse;
    }

    /**
     * @param Ident $ident
     * @param array $ans
     * @return int
     */
    protected function convertIdentGetStatus(Ident $ident, array $ans)
    {
        $status = Ident::STATUS_WAITING;
        $maxTimeWithInnRequest = 60 * 30;
        foreach (['Inn', 'Snils', 'Passport', 'PassportDeferred'] as $key) {
            if(isset($ans[$key])) {
                if(
                    $ans[$key]['Status'] == 'Processing'
                    && $key == 'Inn' && (time() - $ident->DateUpdated) < $maxTimeWithInnRequest
                ) {
                    continue;
                } elseif (in_array($ans[$key]['Status'], ['Processing', 'NotValid']) && $key == 'Inn') {
                    $status = Ident::STATUS_DENIED;
                    break;
                } elseif ($ans[$key]['Status'] == 'Error') {
                    $status = Ident::STATUS_ERROR;
                    break;
                } elseif ($ans[$key]['Status'] == 'NotValid') {
                    $status = Ident::STATUS_DENIED;
                    break;
                } elseif ($ans[$key]['Status'] == 'Valid') {
                    $status = Ident::STATUS_SUCCESS;
                    break;
                }
            }
        }
        return $status;
    }

    /**
     * @throws GateException
     */
    public function currencyExchangeRates()
    {
        throw new GateException('Метод недоступен');
    }

    public function getStatements(GetStatementsForm $getStatementsForm)
    {
        $action = '/api/v1/banking/account/statement/document';

        $getStatementRequest = new GetStatementRequest();
        $getStatementRequest->Account = $this->gate->SchetNumber;
        $getStatementRequest->StartDate = $getStatementsForm->dateFrom;
        $getStatementRequest->EndDate = $getStatementsForm->dateTo;

        if(!$getStatementRequest->validate()) {
            throw new GateException('Некорректные данные запроса');
        }

        $queryData = Json::encode($getStatementRequest->getAttributes());
        $ans = $this->curlXmlReq($queryData, $this->bankUrlXml . $action);

        $getStatementsResponse = new GetStatementsResponse();
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $ans['xml'] = self::array_change_key_case_recursive($ans['xml'], CASE_LOWER);
            if (isset($ans['xml']['documents'])) {
                $getStatementsResponse->status = BaseResponse::STATUS_DONE;
                $getStatementsResponse->statements = $ans['xml']['documents'];
                return $getStatementsResponse;
            }
        }
        $getStatementsResponse->status = BaseResponse::STATUS_ERROR;
        $getStatementsResponse->message = 'Ошибка запроса';
        return $getStatementsResponse;
    }
}
