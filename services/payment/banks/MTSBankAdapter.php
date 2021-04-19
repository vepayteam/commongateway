<?php

namespace app\services\payment\banks;

use app\models\bank\mts_soap\PerfomP2P;
use app\models\bank\mts_soap\RegisterP2P;
use app\models\bank\mts_soap\SoapRequestBuilder;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CheckStatusPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\mts\CheckStatusPayRequest;
use app\services\payment\forms\mts\ConfirmPayRequest;
use app\services\payment\forms\mts\CreatePayRequest;
use app\services\payment\forms\mts\PayOrderRequest;
use app\services\payment\forms\mts\RefundPayRequest;
use app\services\payment\forms\mts\ReversePayRequest;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Carbon\Carbon;
use qfsx\yii2\curl\Curl;
use SoapClient;
use SoapHeader;
use Yii;
use yii\helpers\Json;

class MTSBankAdapter implements IBankAdapter
{
    const AFT_MIN_SUMM = 120000;
    public static $bank = 3;

    /** @var PartnerBankGate */
    protected $gate;

    const BANK_URL = 'https://oplata.mtsbank.ru/payment';
    const BANK_URL_TEST = 'https://web.rbsuat.com/mtsbank';


    private $bankUrl = 'https://oplata.mtsbank.ru/payment';
    private $bankP2PUrl = 'https://oplata.mtsbank.ru/payment/webservices/p2p?wsdl';
    private $bankP2PUrlWsdl = 'https://oplata.mtsbank.ru/payment/webservices/p2p';
    private $bankUrlClient = '';
    private $shopId;
    private $certFile;
    private $keyFile;
    private $caFile;
    private static $orderState = [0 => 'Обрабатывается', 1 => 'Исполнен', 2 => 'Отказано', 3 => 'Возврат'];
    private $backUrls = ['ok' => 'https://api.vepay.online/pay/orderok?orderid='];

    public static $JKHGATE = 0;
    public static $SCHETGATE = 1;
    public static $AFTGATE = 2;
    public static $ECOMGATE = 3;
    public static $VYVODGATE = 4;
    public static $AUTOPAYGATE = 5;
    public static $PEREVODGATE = 6;
    public static $OCTGATE = 7;
    public static $VYVODOCTGATE = 8;
    public static $PEREVODOCTGATE = 9;

    public static $PARTSGATE = 100;

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
     * @return int
     */
    public function getBankId()
    {
        return self::$bank;
    }

    /**
     * Завершение оплаты (запрос статуса)
     *
     * @param string $idpay
     * @param int $org
     * @param bool $isCron
     * @return array [status (1 - оплачен, 2,3 - не оплачен, 0 - в процессе), message, IdPay (id pay_schet), Params]
     * @throws \yii\db\Exception
     */
    public function confirmPay($idpay, $org = 0, $isCron = false)
    {
        $mesg = '';
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($idpay, null, $org);

        if ($params) {
            $state = $params['Status'];
            $ApprovalCode = $RRN = '';
            if ($params['Status'] == 0 && $params['sms_accept'] == 1) {

                $status = $this->checkStatusOrder($params, $isCron);

                if (isset($status['xml']['orderinfo']['statedescription'])) {
                    $mesg = $status['xml']['orderinfo']['statedescription'];
                }

                if ($status['state'] > 0) {
                    //1 - оплачен, 2,3 - не оплачен
                    if (($params['IdUsluga'] == 1 || ($params['IdUser'] > 0 && $params['IdKard'] == 0 && in_array($params['IsCustom'], [TU::$JKH, TU::$ECOM]))) &&
                        $status['state'] == 1 &&
                        isset($status['xml']['orderadditionalinfo']['cardrefid'])
                    ) {
                        //привязка карты через платеж
                        $card = [
                            'number' => str_replace(" ", "", $status['xml']['orderadditionalinfo']['cardnumber']),
                            'expiry' => $status['xml']['orderadditionalinfo']['cardexpmonth'] . substr($status['xml']['orderadditionalinfo']['cardexpyear'], 2, 2),
                            'idcard' => $status['xml']['orderadditionalinfo']['cardrefid'],
                            //'type' => isset($status['xml']['orderadditionalinfo']['cardbrand']) ? $this->GetCardType($status['xml']['orderadditionalinfo']['cardbrand']) : 0,
                            'type' => Cards::GetTypeCard($status['xml']['orderadditionalinfo']['cardnumber']),
                            'holder' => isset($status['xml']['orderadditionalinfo']['cardholder']) ? $status['xml']['orderadditionalinfo']['cardholder'] : ''
                        ];
                        $payschets->UpdateCardExtId($params['IdUser'], $card, $params['ID'], MTSBankAdapter::$bank);
                    }

                    if ($status['state'] == 1) {
                        //$ApprovalCode = isset($status['xml']['APPROVAL_CODE']) ? $status['xml']['APPROVAL_CODE'] : '';
                        $RRN = isset($status['xml']['orderadditionalinfo']['rrn']) ? $status['xml']['orderadditionalinfo']['rrn'] : '';
                    }

                    $payschets->confirmPay([
                        'idpay' => $params['ID'],
                        'idgroup' => $params['IdGroupOplat'],
                        'result_code' => $status['state'],
                        'trx_id' => $params['ExtBillNumber'],
                        'ApprovalCode' => $ApprovalCode,
                        'RRN' => $RRN,
                        'message' => $mesg
                    ]);
                }
                $state = $status['state'];
            } elseif (in_array($state, [1, 2, 3])) {
                $mesg = $params['ErrorInfo'];
                //$ApprovalCode = $params['ApprovalCode'];
                $RRN = $params['RRN'];
            }
            return [
                'status' => $state,
                'message' => $mesg,
                'IdPay' => $params['ID'],
                'Params' => $params,
                'info' => ['card' => $params['CardNum'], 'brand' => $params['CardType'], 'rrn' => $RRN, 'transact' => $params['ExtBillNumber']]
            ];
        }
        return ['status' => 0, 'message' => $mesg, 'IdPay' => 0, 'Params' => null, 'info' => null];
    }

    /**
     * @inheritDoc
     */
    public function transferToCard(array $data)
    {
        throw new \Exception('Перечисление денег на карту через МТС банк недоступен');
//        $payschets = new Payschets();
//        $params = $payschets->getSchetData($data['IdPay']);
//
//        $formData = [
//            'amount' => (int)$params['SummFull'],
//            'orderNumber' => $params['ID'],
//            'orderDescription' => $params['NameUsluga'],
//            'returnUrl' => $this->backUrls['ok'] . $params['ID'],
//            'failUrl' => $this->backUrls['ok'] . $params['ID'],
//            'transactionTypeIndicator' => 'D',
//            'type' => 'WITHOUT_FROM_CARD',
//            'features' => [
//                'feature' => 'WITHOUT_FROM_CARD',
//            ]
//        ];
//
//        $registerP2P = new RegisterP2P();
//        if(!$registerP2P->load($formData, '') || !$registerP2P->validate()) {
//            throw new \Exception('Ошибка данных');
//        }
//
//        $partner = Partner::findOne(['ID' => $params['IDPartner']]);
//
//        $response = (new SoapRequestBuilder($this->bankP2PUrl, 'registerP2P', $registerP2P))
//            ->addSecurity($partner->MtsLoginOct, $partner->MtsPasswordOct)
//            ->addBody()
//            ->sendRequest();
//
//        $responseReturn = $response->xpath('//Body/registerP2PResponse/return')[0];
//        $errorResponse = (string)$responseReturn->attributes()['errorCode'];
//        $errorMessage = (string)$responseReturn->attributes()['errorMessage'];
//
//        if($errorResponse != 0) {
//            throw new \Exception($errorMessage);
//        }
//        $orderId = (string)$response->xpath('//Body/registerP2PResponse/return/orderId')[0];
//
//
//        $data = [
//            'orderId' => $orderId,
//            'type' => 'WITHOUT_FROM_CARD',
//            'toCard' => [
//                'pan' => $data['CardNum'],
//                'cvc' => '',
//                'expirationYear' => '',
//                'expirationMonth' => '',
//                'cardholderName' => '',
//            ],
//        ];
//
//        $performP2P = new PerfomP2P();
//        if(!$performP2P->load($data, '') || !$performP2P->validate()) {
//            throw new \Exception('Ошибка данных');
//        }
//
//        $response = (new SoapRequestBuilder($this->bankP2PUrl, 'performP2P', $performP2P))
//            ->addSecurity($partner->MtsLoginOct, $partner->MtsPasswordOct)
//            ->addBody()
//            ->sendRequest();
    }

    /**
     * Оплата без формы (PCI DSS)
     * @param array $params
     * @return array
     */
    public function PayXml(array $params)
    {
        $ret = $this->RegisterOrder($params);
        if ($ret['status'] == 1) {
            $ret = $this->PayOrder($params, $ret['transac']);
        }
        return $ret;
    }

    /**
     * Оплата ApplePay (без формы)
     * @param array $params
     * @return array
     */
    public function PayApple(array $params)
    {
        $ret = $this->RegisterOrder($params);
        if ($ret['status'] == 1) {

            $action = '/rest/payment.do';
            $queryData = [
                'userName' => $this->shopId,
                'password' => $this->certFile,
                'merchant' => $params['Apple_MerchantID'],
                'orderNumber' => $params['ExtBillNumber'],
                'description' => 'Оплата по счету ' . $params['ID'],
                'paymentToken' => $params['PaymentToken']
            ];

            $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

            if (isset($ans['xml']) && !empty($ans['xml'])) {
                if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                    return [
                        'status' => 1,
                        'transac' => $params['ExtBillNumber'],
                    ];
                } else {
                    $error = $ans['xml']['errorCode'];
                    $message = $ans['xml']['errorMessage'];
                    return ['status' => 2, 'message' => $error . ":" . $message, 'fatal' => 1];
                }
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    /**
     * Оплата GooglePay
     * @param array $params
     * @return array
     */
    public function PayGoogle(array $params)
    {
        $ret = $this->RegisterOrder($params);
        if ($ret['status'] == 1) {

            $action = '/rest/payment.do';
            $queryData = [
                'userName' => $this->shopId,
                'password' => $this->certFile,
                'orderNumber' => $params['ExtBillNumber'],
                'description' => 'Оплата по счету ' . $params['ID'],
                'paymentToken' => $params['PaymentToken']
            ];

            $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

            if (isset($ans['xml']) && !empty($ans['xml'])) {
                if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                    return [
                        'status' => 1,
                        'transac' => $params['ExtBillNumber'],
                        'termUrl' => $ans['xml']['data']['termUrl'] ?? null,
                        'acsUrl' => $ans['xml']['data']['acsUrl'] ?? null,
                        'paReq' => $ans['xml']['data']['paReq'] ?? null,
                        'md' => $params['ExtBillNumber'] ?? null
                    ];
                } else {
                    $error = $ans['xml']['errorCode'];
                    $message = $ans['xml']['errorMessage'];
                    return ['status' => 2, 'message' => $error . ":" . $message, 'fatal' => 1];
                }
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    /**
     * Оплата SamsungPay
     * @param array $params
     * @return array
     */
    public function PaySamsung(array $params)
    {
        $ret = $this->RegisterOrder($params);
        if ($ret['status'] == 1) {

            $action = '/rest/payment.do';
            $queryData = [
                'userName' => $this->shopId,
                'password' => $this->certFile,
                'orderNumber' => $params['ExtBillNumber'],
                'description' => 'Оплата по счету ' . $params['ID'],
                'paymentToken' => $params['PaymentToken']
            ];

            $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

            if (isset($ans['xml']) && !empty($ans['xml'])) {
                if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                    return [
                        'status' => 1,
                        'transac' => $params['ExtBillNumber'],
                    ];
                } else {
                    $error = $ans['xml']['errorCode'];
                    $message = $ans['xml']['errorMessage'];
                    return ['status' => 2, 'message' => $error . ":" . $message, 'fatal' => 1];
                }
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    /**
     * Финиш оплаты без формы (PCI DSS)
     * @param array $params
     * @return array
     */
    public function ConfirmXml(array $params)
    {
        $action = '/rest/finish3dsPayment.do';
        $queryData = [
            'userName' => $this->shopId,
            'password' => $this->certFile,
            'mdOrder' => $params['ExtBillNumber'],
            'paRes' => $params['PaRes']
        ];

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                return [
                    'status' => 1,
                    'transac' => $params['ExtBillNumber'],
                ];
            } else {
                $error = $ans['xml']['errorCode'];
                $message = $ans['xml']['errorMessage'];
                return ['status' => 2, 'message' => $error.":".$message, 'fatal' => 1];
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    /**
     * Возврат оплаты
     * @param int $IdPay
     * @return array
     * @throws \yii\db\Exception
     */
    public function reversOrder($IdPay)
    {
        $payschets = new Payschets();
        //данные счета
        $params = $payschets->getSchetData($IdPay);

        if ($params['Status'] == 1) {

            if ($params['DateCreate'] < mktime(0, 0, 0, date('n'), date('d'), date('Y'))) {
                $action = '/rest/refund.do';
                $queryData = [
                    'userName' => $this->shopId,
                    'password' => $this->certFile,
                    'orderId' => $params['ExtBillNumber'],
                    'amount' => $params['SummFull']
                ];
            } else {
                $action = '/rest/reverse.do';
                $queryData = [
                    'userName' => $this->shopId,
                    'password' => $this->certFile,
                    'orderId' => $params['ExtBillNumber']
                ];
            }

            $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

            if (isset($ans['xml']) && !empty($ans['xml'])) {
                if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                    return [
                        'status' => 1,
                        'Status' => 0,
                        'message' => ''
                    ];
                } else {
                    $error = $ans['xml']['errorCode'];
                    $message = $ans['xml']['errorMessage'];
                    return ['state' => $error == 0, 'Status' => $error, 'message' => $error.":".$message];
                }
            }
        }
        return ['state' => 0, 'Status' => '', 'message' => ''];
    }

    private function RegisterOrder(array $params)
    {
        $action = '/rest/register.do';
        $queryData = [
            'userName' => $this->shopId,
            'password' => $this->certFile,
            'orderNumber' => $params['ID'],
            'amount' => $params['SummFull'],
            'description' => 'Оплата по счету ' . $params['ID'],
            'returnUrl' => $this->backUrls['ok'] . $params['ID'],
            'sessionTimeoutSecs' => $params['TimeElapsed']
        ];

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $ordernumber = $ans['xml']['orderId'];
                return ['status' => 1, 'transac' => $ordernumber];
            } else {
                $error = $ans['xml']['errorCode'];
                $message = $ans['xml']['errorMessage'];
                return ['status' => 2, 'message' => $error.":".$message, 'fatal' => 1];
            }
        }
        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    // TODO: refact DRY
    private function RegisterTransferToCard(array $params)
    {
        $action = '/registerP2P';
        $queryData = [
            'userName' => $this->shopId,
            'password' => $this->certFile,
            'orderNumber' => $params['ID'],
            'amount' => $params['SummFull'],
            'description' => 'Оплата по счету ' . $params['ID'],
            'returnUrl' => $this->backUrls['ok'] . $params['ID'],
            'sessionTimeoutSecs' => $params['TimeElapsed'],
            'features' => 'WITHOUT_FROM_CARD',
        ];

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $ordernumber = $ans['xml']['orderId'];
                return ['status' => 1, 'transac' => $ordernumber];
            } else {
                $error = $ans['xml']['errorCode'];
                $message = $ans['xml']['errorMessage'];
                return ['status' => 2, 'message' => $error.":".$message, 'fatal' => 1];
            }
        }
        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    private function PayOrder(array $params, $ordernumber)
    {
        $action = '/rest/paymentorder.do';
        $queryData = [
            'userName' => $this->shopId,
            'password' => $this->certFile,
            'MDORDER' => $ordernumber,
            '$PAN' => $params['card']['number'],
            '$CVC' => $params['card']['cvc'],
            'YYYY' => (int)("20" . $params['card']['year']),
            'MM' => (int)($params['card']['month']),
            'TEXT' => $params['card']['holder'],
            'language' => 'ru',
            'ip' => $params['IPAddressUser']
        ];

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $url = $ans['xml']['acsUrl'] ?? '';
                $pa = $ans['xml']['paReq'] ?? '';
                $md = $ordernumber;
                return [
                    'status' => 1,
                    'transac' => $ordernumber,
                    'url' => $url,
                    'pa' => $pa,
                    'md' => $md
                ];
            } else {
                $error = $ans['xml']['errorCode'];
                $message = $ans['xml']['errorMessage'];
                return ['status' => 2, 'message' => $error.":".$message, 'fatal' => 1];
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];

    }

    /**
     * Проверка статуса заказа
     * @param array $params [ID, IsCustom]
     * @param bool $isCron
     * @return array [state, xml]
     */
    private function checkStatusOrder($params, $isCron)
    {
        $action = '/rest/getOrderStatusExtended.do';
        $queryData = [
            'userName' => $this->shopId,
            'password' => $this->certFile,
            'orderId' => $params['ExtBillNumber'],
        ];

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $status = $this->convertState($ans['xml']['orderStatus']);
                return [
                    'state' => $status,
                    'xml' => [
                        'orderinfo' => [
                            'statedescription' => $ans['xml']['actionCodeDescription']
                        ],
                        'orderadditionalinfo' => [
                            'rrn' => $ans['xml']['authRefNum'] ?? null,
                            'cardnumber' => $ans['xml']['cardAuthInfo']['maskedPan'] ?? null,
                            'expiry' => isset($ans['xml']['cardAuthInfo']['expiration']) ? substr($ans['xml']['cardAuthInfo']['expiration'], 4,2).substr($ans['xml']['cardAuthInfo']['expiration'], 2,2) : null,
                            'idcard' => $ans['xml']['cardAuthInfo']['approvalCode'] ?? null,
                            'type' => Cards::GetTypeCard($ans['xml']['cardAuthInfo']['maskedPan']),
                            'holder' => $ans['xml']['cardAuthInfo']['cardholderName'] ?? null,
                        ]
                    ]
                ];
            } else {
                $error = $ans['xml']['errorCode'];
                $message = $ans['xml']['errorMessage'];
                if ($error == 6) {
                    //не найден в банке - если в кроне запрос, то отменить
                    if ($isCron && isset($params['IsCustom']) && TU::IsInPay($params['IsCustom'])) {
                        //не найден в банке - если в кроне запрос, то отменить
                        return ['state' => 2, 'xml' => ['orderinfo' => ['statedescription' => 'Платеж не проведен']]];
                    } else {
                        return ['state' => 0, 'xml' => ['orderinfo' => ['statedescription' => 'В обработке']]];
                    }
                } else {
                    return ['state' => 0, 'xml' => ['orderinfo' => ['statedescription' => $message]]];
                }
            }
        }
        return ['state' => 0];
    }

    /**
     * Статус в наш: 0 - Обрабатывается 1 - оплачен 2,3 - не оплачен
     * @param int $orderStatus
     * @return int
     */
    private function convertState($orderStatus)
    {
        switch ($orderStatus) {
            case 0: //Заказ зарегистрирован, но не оплачен;
                return 0;
            case 1: //Предавторизованная сумма захолдирована (для двухстадийных платежей);
                return 0;
            case 2: //Проведена полная авторизация суммы заказа;
                return 1;
            case 3: //Авторизация отменена;
            case 4: //По транзакции была проведена операция возврата;
                return 3;
            case 5: //Инициирована авторизация через ACS банка-эмитента;
                return 0;
            case 6: //Авторизация отклонена.
                return 2;
        }
        return 0;
    }

    /**
     * Отправка POST запроса в банк
     * @param array $postArr
     * @param string $url
     * @param array $addHeader
     * @return array [xml, error]
     */
    private function curlXmlReq(array $postArr, $url, $addHeader = [])
    {
        $post = http_build_query($postArr);
        $timout = 110;
        $curl = new Curl();
        Yii::warning("req: login = " . $this->gate->Login . " url = " . $url . "\r\n" . $this->MaskLog($post), 'merchant');
        try {
            $curl->reset()
                ->setOption(CURLOPT_TIMEOUT, $timout)
                ->setOption(CURLOPT_CONNECTTIMEOUT, $timout)
                ->setOption(CURLOPT_HTTPHEADER, array_merge([
                    'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
                ], $addHeader))
                ->setOption(CURLOPT_SSL_VERIFYHOST, false)
                ->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv1')
                //->setOption(CURLOPT_SSLKEY, $this->keyFile)
                //->setOption(CURLOPT_SSLCERT, $this->certFile)
                //->setOption(CURLOPT_CAINFO, $this->caFile)
                ->setOption(CURLOPT_SSL_VERIFYPEER, false)
                ->setOption(CURLOPT_POSTFIELDS, $post)
                ->post($url);
        } catch (\Exception $e) {
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
                    $ans['xml'] = Json::decode($curl->response);
                    break;
                case 500:
                    $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
                    $ans['httperror'] = Json::decode($curl->response);
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

    private function MaskLog($post)
    {
        if (preg_match('/PAN=(\d+)/ius', $post, $m)) {
            $post = str_ireplace($m[1], Cards::MaskCard($m[1]), $post);
        }
        if (preg_match('/CVC=(\d+)/ius', $post, $m)) {
            $post = str_ireplace($m[1], "***", $post);
        }
        if (preg_match('/password=([^\&]+)/ius', $post, $m)) {
            $post = str_ireplace($m[1], "***", $post);
        }
        return $post;
    }

    /**
     * @param CreatePayForm $createPayForm
     * @return CreatePayResponse
     */
    public function createPay(CreatePayForm $createPayForm)
    {
        $createPayResponse = $this->_registerOrder($createPayForm->getPaySchet());

        if ($createPayResponse->status == BaseResponse::STATUS_DONE) {
            $createPayResponse = $this->_payOrder($createPayForm, $createPayResponse);
        }
        return $createPayResponse;
    }

    /**
     * @param PaySchet $paySchet
     * @return CreatePayResponse
     */
    protected function _registerOrder(PaySchet $paySchet)
    {
        $action = '/rest/register.do';

        $createPayRequest = new CreatePayRequest();
        $createPayRequest->userName = $this->gate->Login;
        $createPayRequest->password = $this->gate->Password;
        $createPayRequest->orderNumber = $paySchet->ID;
        $createPayRequest->amount = $paySchet->getSummFull();
        $createPayRequest->description = 'Оплата по счету ' . $paySchet->ID;
        $createPayRequest->returnUrl = Yii::$app->params['domain'] . '/pay/orderok?orderid=' . $paySchet->ID;
        $createPayRequest->sessionTimeoutSecs = $paySchet->TimeElapsed;

        $queryData = $createPayRequest->getAttributes();

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        $createPayResponse = new CreatePayResponse();
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $createPayResponse->status = BaseResponse::STATUS_DONE;
                $createPayResponse->transac = $ans['xml']['orderId'];
            } else {
                $createPayResponse->status = BaseResponse::STATUS_ERROR;
                $createPayResponse->message = $ans['xml']['errorMessage'];
                $createPayResponse->fatal = 1;
            }
        } else {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = 'Ошибка запроса, попробуйте повторить позднее';
            $createPayResponse->fatal = 0;
        }
        return $createPayResponse;
    }

    /**
     * @param CreatePayForm $createPayForm
     * @param CreatePayResponse $createPayResponse
     * @return CreatePayResponse
     */
    protected function _payOrder(CreatePayForm $createPayForm, CreatePayResponse $createPayResponse)
    {
        $action = '/rest/paymentorder.do';

        $payOrderRequest = new PayOrderRequest();
        $payOrderRequest->userName = $this->gate->Login;
        $payOrderRequest->password = $this->gate->Password;
        $payOrderRequest->MDORDER = $createPayResponse->transac;
        $payOrderRequest->PAN = $createPayForm->CardNumber;
        $payOrderRequest->CVC = $createPayForm->CardCVC;
        $payOrderRequest->YYYY = (int)('20' . $createPayForm->CardYear);
        $payOrderRequest->MM = (int)$createPayForm->CardMonth;
        $payOrderRequest->TEXT = $createPayForm->CardHolder;
        $payOrderRequest->language = 'ru';
        $payOrderRequest->ip = $createPayForm->getPaySchet()->IPAddressUser;


        $queryData = $payOrderRequest->getAttributes();

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $createPayResponse->status = BaseResponse::STATUS_DONE;
                $createPayResponse->url = $ans['xml']['acsUrl'] ?? '';
                $createPayResponse->pa = $ans['xml']['paReq'] ?? '';
                $createPayResponse->md = $createPayResponse->transac;
            } else {
                $createPayResponse->status = BaseResponse::STATUS_ERROR;
                $createPayResponse->message = $ans['xml']['errorCode'] . ':' . $ans['xml']['errorMessage'];
                $createPayResponse->fatal = 1;
            }
        } else {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = 'Ошибка запроса, попробуйте повторить позднее';
            $createPayResponse->fatal = 0;

        }
        return $createPayResponse;
    }

    /**
     * @param DonePayForm $donePayForm
     * @return ConfirmPayResponse
     */
    public function confirm(DonePayForm $donePayForm)
    {
        $action = '/rest/finish3dsPayment.do';

        $confirmPayRequest = new ConfirmPayRequest();
        $confirmPayRequest->userName = $this->gate->Login;
        $confirmPayRequest->password = $this->gate->Password;
        $confirmPayRequest->mdOrder = $donePayForm->getPaySchet()->ExtBillNumber;
        $confirmPayRequest->paRes = $donePayForm->paRes;

        $queryData = $confirmPayRequest->getAttributes();

        $ans = $this->curlXmlReq($queryData, $this->bankUrl.$action);

        $confirmPayResponse = new ConfirmPayResponse();
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $confirmPayResponse->status = BaseResponse::STATUS_DONE;
                $confirmPayResponse->transac = $donePayForm->getPaySchet()->ExtBillNumber;
            } else {
                $confirmPayResponse->status = BaseResponse::STATUS_ERROR;
                $confirmPayResponse->message = $ans['xml']['errorCode'] . ":" . $ans['xml']['errorMessage'];
            }
        } else {
            $confirmPayResponse->status = BaseResponse::STATUS_ERROR;
            $confirmPayResponse->message = 'Ошибка запроса, попробуйте повторить позднее';
        }

        return $confirmPayResponse;
    }

    /**
     * @param OkPayForm $okPayForm
     * @return CheckStatusPayResponse
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        $action = '/rest/getOrderStatusExtended.do';
        $checkStatusPayRequest = new CheckStatusPayRequest();
        $checkStatusPayRequest->userName = $this->gate->Login;
        $checkStatusPayRequest->password = $this->gate->Password;
        $checkStatusPayRequest->orderId = $okPayForm->getPaySchet()->ExtBillNumber;

        $queryData = $checkStatusPayRequest->getAttributes();
        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        $checkStatusPayResponse = new CheckStatusPayResponse();
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $status = $this->convertState($ans['xml']['orderStatus']);

                $checkStatusPayResponse->status = $status;
                $checkStatusPayResponse->xml = [
                    'orderinfo' => [
                        'statedescription' => $ans['xml']['actionCodeDescription']
                    ],
                    'orderadditionalinfo' => [
                        'rrn' => $ans['xml']['authRefNum'] ?? null,
                        'cardnumber' => $ans['xml']['cardAuthInfo']['maskedPan'] ?? null,
                        'expiry' => isset($ans['xml']['cardAuthInfo']['expiration']) ? substr($ans['xml']['cardAuthInfo']['expiration'], 4,2).substr($ans['xml']['cardAuthInfo']['expiration'], 2,2) : null,
                        'idcard' => $ans['xml']['cardAuthInfo']['approvalCode'] ?? null,
                        'type' => Cards::GetTypeCard($ans['xml']['cardAuthInfo']['maskedPan']),
                        'holder' => $ans['xml']['cardAuthInfo']['cardholderName'] ?? null,
                    ]
                ];
            } else {
                $error = $ans['xml']['errorCode'];
                $message = $ans['xml']['errorMessage'];

                // TODO: const
                if ($error == 6) {
                    //не найден в банке - если в кроне запрос, то отменить
                    if (Yii::$app instanceof \yii\console\Application
                        && isset($okPayForm->getPaySchet()->uslugatovar->IsCustom)
                        && TU::IsInPay($okPayForm->getPaySchet()->uslugatovar->IsCustom)
                    ) {
                        //не найден в банке - если в кроне запрос, то отменить
                        $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                        $checkStatusPayResponse->xml = [
                            'orderinfo' => [
                                'statedescription' => 'Платеж не проведен',
                            ]
                        ];
                    } else {
                        $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
                        $checkStatusPayResponse->xml = [
                            'orderinfo' => [
                                'statedescription' => 'В обработке',
                            ]
                        ];
                    }
                } else {
                    $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
                    $checkStatusPayResponse->xml = [
                        'orderinfo' => [
                            'statedescription' => $message,
                        ]
                    ];
                }
            }
        }

        return $checkStatusPayResponse;
    }

    /**
     * @param AutoPayForm $autoPayForm
     * @return CreateRecurrentPayResponse|void
     * @throws GateException
     */
    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @param RefundPayForm $refundPayForm
     * @return RefundPayResponse
     */
    public function refundPay(RefundPayForm $refundPayForm)
    {
        $paySchet = $refundPayForm->paySchet;

        $refundPayResponse = new RefundPayResponse();
        if($paySchet->Status !== PaySchet::STATUS_DONE) {
            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
            return $refundPayResponse;
        }

        $action = '/rest/reverse.do';
        /** @var ReversePayRequest $requestForm */
        $requestForm = new ReversePayRequest();
        if($paySchet->DateCreate < Carbon::now()->startOfDay()->timestamp) {
            $action = '/rest/refund.do';
            $requestForm = new RefundPayRequest();
            $requestForm->amount = $paySchet->getSummFull();
        }

        $requestForm->userName = $this->gate->Login;
        $requestForm->password = $this->gate->Password;
        $requestForm->orderId = $paySchet->ExtBillNumber;

        $ans = $this->curlXmlReq($requestForm->getAttributes(), $this->bankUrl.$action);
        $refundPayResponse = new RefundPayResponse();
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            if (!isset($ans['xml']['errorCode']) || $ans['xml']['errorCode'] == 0) {
                $refundPayResponse->status = BaseResponse::STATUS_DONE;
            } else {
                $error = $ans['xml']['errorCode'];
                $message = $ans['xml']['errorMessage'];
                $refundPayResponse->status = BaseResponse::STATUS_ERROR;
                $refundPayResponse->message = $error . " : " . $message;
            }
        }
        return $refundPayResponse;
    }

    /**
     * @inheritDoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function getAftMinSum()
    {
        return self::AFT_MIN_SUMM;
    }

    /**
     * @inheritDoc
     */
    public function getBalance(GetBalanceForm $getBalanceForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function transferToAccount(OutPayaccForm $outPayaccForm)
    {
        throw new GateException('Метод недоступен');
    }
}
