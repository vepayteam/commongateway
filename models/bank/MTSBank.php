<?php

namespace app\models\bank;

use app\models\bank\mts_soap\PerfomP2P;
use app\models\bank\mts_soap\RegisterP2P;
use app\models\bank\mts_soap\SoapRequestBuilder;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\Payschets;
use app\models\TU;
use qfsx\yii2\curl\Curl;
use SoapClient;
use SoapHeader;
use Yii;
use yii\helpers\Json;

class MTSBank implements IBank
{
    public static $bank = 3;

    private $bankUrl = 'https://oplata.mtsbank.ru/payment';
    private $bankP2PUrl = 'https://oplata.mtsbank.ru/payment/webservices/p2p?wsdl';
    private $bankP2PUrlWsdl = 'https://oplata.mtsbank.ru/payment/webservices/p2p';
    private $bankUrlClient = '';
    private $shopId = 'vepay-api';
    private $certFile = 'vepay';
    private $keyFile = 'ma5m5b0vn7ucd1q4njsmceuul1';
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

    /**
     * MTSBank constructor
     * @param MtsGate|null $mtsGate
     * @throws \yii\db\Exception
     */
    public function __construct($mtsGate = null)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y') {
            $this->bankUrl = 'https://web.rbsuat.com/mtsbank';
            $this->bankP2PUrl = 'https://web.rbsuat.com/mtsbank/webservices/p2p';
            $this->bankP2PUrlWsdl = 'https://web.rbsuat.com/mtsbank/webservices/p2p?wsdl';
            $this->backUrls['ok'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/pay/orderok?orderid=';
        }

        if ($mtsGate) {
            $this->SetMfoGate($mtsGate);
        }
    }

    public function SetMfoGate(MtsGate $mtsGate)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y') {
            return;
        }

        if (in_array($mtsGate->getTypeGate(), [self::$OCTGATE, self::$SCHETGATE]) && !empty($params['LoginTkbOct'])) {
            //выдача на карту OCT, и на счет
            $this->shopId = $mtsGate->gates['MtsLogin'];
            $this->certFile = $mtsGate->gates['MtsPassword'];
            $this->keyFile = $mtsGate->gates['MtsToken'];
        } elseif ($mtsGate->getTypeGate() == self::$AFTGATE && !empty($params['LoginTkbAft'])) {
            //прием с карты AFT
            $this->shopId = $mtsGate->gates['MtsLoginAft'];
            $this->certFile = $mtsGate->gates['MtsPasswordAft'];
            $this->keyFile = $mtsGate->gates['MtsTokenAft'];
        } elseif ($mtsGate->getTypeGate() == self::$ECOMGATE && !empty($params['LoginTkbEcom'])) {
            //ecom
            $this->shopId = $mtsGate->gates['MtsLogin'];
            $this->certFile = $mtsGate->gates['MtsPassword'];
            $this->keyFile = $mtsGate->gates['MtsToken'];
        } elseif ($mtsGate->getTypeGate() == self::$VYVODGATE && !empty($params['LoginTkbVyvod'])) {
            //вывод платежей
            $this->shopId = $mtsGate->gates['MtsLogin'];
            $this->certFile = $mtsGate->gates['MtsPassword'];
            $this->keyFile = $mtsGate->gates['MtsToken'];
        } elseif ($mtsGate->getTypeGate() == self::$JKHGATE && !empty($params['LoginTkbJkh'])) {
            //жкх платежи
            $this->shopId = $mtsGate->gates['MtsLoginJkh'];
            $this->certFile = $mtsGate->gates['MtsPasswordJkh'];
            $this->keyFile = $mtsGate->gates['MtsTokenJkh'];
        } elseif ($mtsGate->getTypeGate() == self::$AUTOPAYGATE) {
            //авторплатеж
            $gateAutoId = $params['AutoPayIdGate'] ?? 0;
            if ($gateAutoId && !empty($params['LoginTkbAuto'.intval($gateAutoId)])) {
                $this->shopId = $mtsGate->gates['MtsLogin'];
                $this->certFile = $mtsGate->gates['MtsPassword'];
                $this->keyFile = $mtsGate->gates['MtsToken'];
            }
        } elseif ($mtsGate->getTypeGate() == self::$PEREVODGATE && !empty($params['LoginTkbPerevod'])) {
            //перевод зарезервированной комиссии обратно
            $this->shopId = $mtsGate->gates['MtsLogin'];
            $this->certFile = $mtsGate->gates['MtsPassword'];
            $this->keyFile = $mtsGate->gates['MtsToken'];
        } elseif ($mtsGate->getTypeGate() == self::$VYVODOCTGATE && !empty($params['LoginTkbOctVyvod'])) {
            //вывод со счета выплат
            $this->shopId = $mtsGate->gates['MtsLoginOct'];
            $this->certFile = $mtsGate->gates['MtsPasswordOct'];
            $this->keyFile = $mtsGate->gates['MtsTokenOct'];
        } elseif ($mtsGate->getTypeGate() == self::$PEREVODOCTGATE && !empty($params['LoginTkbOctPerevod'])) {
            //перевод со счета выплат внутри банка
            $this->shopId = $mtsGate->gates['MtsLogin'];
            $this->certFile = $mtsGate->gates['MtsPassword'];
            $this->keyFile = $mtsGate->gates['MtsToken'];
        }
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

                //шлюз
                if ($params['IdUsluga'] == 1) {
                    $MtsGate = new MtsGate($params['IdOrg'], self::$AUTOPAYGATE);
                } else {
                    $MtsGate = new MtsGate($params['IDPartner'], null, $params['IsCustom']);
                }
                if ($params['AutoPayIdGate']) {
                    $MtsGate->AutoPayIdGate = $params['AutoPayIdGate'];
                }
                $this->SetMfoGate($MtsGate);

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
                        $payschets->UpdateCardExtId($params['IdUser'], $card, $params['ID'], MtsBank::$bank);
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
            'token' => $this->keyFile,
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
            'token' => $this->keyFile,
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
            //'orderId' => $params['ExtBillNumber'],
            'orderNumber' => $params['ID']
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
        Yii::warning("req: login = " . $this->shopId . " url = " . $url . "\r\n" . $this->MaskCardLog($post), 'merchant');
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

    private function MaskCardLog($post)
    {
        if (preg_match('/PAN=(\d+)/ius', $post, $m)) {
            $post = str_ireplace($m[1], Cards::MaskCard($m[1]), $post);
        }
        if (preg_match('/CVC=(\d+)/ius', $post, $m)) {
            $post = str_ireplace($m[1], "***", $post);
        }
        return $post;
    }

}
