<?php

namespace app\models\bank;

use app\models\payonline\Cards;
use app\models\Payschets;
use app\models\TU;
use DOMDocument;
use DOMNode;
use DOMXPath;
use qfsx\yii2\curl\Curl;
use Yii;

class MTSBank implements IBank
{
    public static $bank = 3;

    private $bankUrl = 'https://pay.mtsbank.ru/webservices/merchant';
    private $bankUrlClient = 'https://pay.mtsbank.ru';
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

    /* @var DOMDocument $doc */
    private $doc;

    /**
     * MTSBank constructor
     * @param MtsGate|null $mtsGate
     * @throws \yii\db\Exception
     */
    public function __construct($mtsGate = null)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y') {
            $this->bankUrl = 'https://test.paymentgate.ru:443/webservices/merchant-ws';
        }

        if ($mtsGate) {
            $this->SetMfoGate($mtsGate);
        }
    }

    public function SetMfoGate($mtsGate)
    {

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
                    $MtsGate = new TcbGate($params['IdOrg'], self::$AUTOPAYGATE);
                } else {
                    $MtsGate = new TcbGate($params['IDPartner'], null, $params['IsCustom']);
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
     * Финиш оплаты без формы (PCI DSS)
     * @param array $params
     * @return array
     */
    public function ConfirmXml(array $params)
    {
        //finishThreeDs
        //finishThreeDsResponse

        $body = $this->CreateSoap();
        $finishThreeDs = $this->doc->createElementNS('http://engine.paymentgate.ru/webservices/merchant', 'mer:finishThreeDs');
        $body->appendChild($finishThreeDs);
        $request = $this->doc->createElement('request');
        $finishThreeDs->appendChild($request);
        //$request->setAttribute("merchantOrderNumber", $params['ID']);
        $request->setAttribute("language", "ru");
        $request->setAttribute("md", $params['MD']);
        $request->setAttribute("paRes", $params['PaRes']);

        $ans = $this->curlXmlReq($this->doc->saveXML(), $this->bankUrl);

        $ans['xml'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
<soap:Body> 
<ns1:finishThreeDsResponse xmlns:ns1="http://engine.paymentgate.ru/webservices/merchant"> 
<return errorMessage="" errorCode="0" returnUrl="http://ya.ru?orderId=8b5b7ee5-eb5a-4cf4-81ec-7153f7ca2864"/> 
</ns1:finishThreeDsResponse> 
</soap:Body> 
</soap:Envelope>';

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $return = $this->ParseResult($ans['xml'], 'registerOrderResponse');
            if ($return) {
                $error = $return->attributes->getNamedItem('errorCode')->nodeValue;
                if ($error == 0) {
                    $ordernumber = $return->attributes->getNamedItem('orderId')->nodeValue;
                    return ['status' => 1, 'transac' => $ordernumber];
                } else {
                    $message = $return->attributes->getNamedItem('errorMessage')->nodeValue;
                    return ['status' => 2, 'message' => $message, 'fatal' => 1];
                }
            } else {
                $fault = $this->ParseFault($ans['xml']);
                return ['status' => 2, 'message' => $fault->nodeValue, 'fatal' => 1];
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

            $body = $this->CreateSoap();
            $order = $this->doc->createElement('order');
            $order->setAttribute("orderId", $params['ID']);
            if ($params['DateCreate'] < mktime(0, 0, 0, date('n'), date('d'), date('Y'))) {
                //возврат - отмена на следующий день после оплаты
                $order->setAttribute("refundAmount", $params['SummFull']);
                $refundOrder = $this->doc->createElementNS('http://engine.paymentgate.ru/webservices/merchant', 'mer:refundOrder');
                $refundOrder->appendChild($order);
                $body->appendChild($refundOrder);
            } else {
                //отмена в день оплаты
                $reverseOrder = $this->doc->createElementNS('http://engine.paymentgate.ru/webservices/merchant', 'mer:reverseOrder');
                $reverseOrder->appendChild($order);
                $body->appendChild($reverseOrder);
            }

            $ans = $this->curlXmlReq($this->doc->saveXML(), $this->bankUrl);

            $ans['xml'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
<soap:Body> 
<ns1:refundOrderResponse xmlns:ns1="http://engine.paymentgate.ru/webservices/merchant"> 
  <return errorCode="7" errorMessage="    "/> 
</ns1:refundOrderResponse> 
</soap:Body> 
</soap:Envelope>
';
            if (isset($ans['xml']) && !empty($ans['xml'])) {
                $return = $this->ParseResult($ans['xml'], 'paymentOrderResponse');
                if ($return) {
                    $error = $return->attributes->getNamedItem('errorCode')->nodeValue;
                    $message = $return->attributes->getNamedItem('errorMessage')->nodeValue;

                    return ['state' => $error == 0, 'Status' => $error, 'message' => $message];
                }
            }
        }
        return ['state' => 0, 'Status' => '', 'message' => ''];
    }

    private function RegisterOrder(array $params)
    {
        $body = $this->CreateSoap();
        $registerOrder = $this->doc->createElementNS('http://engine.paymentgate.ru/webservices/merchant', 'mer:registerOrder');
        $body->appendChild($registerOrder);
        $order = $this->doc->createElement('order');
        $registerOrder->appendChild($order);
        $order->setAttribute("merchantOrderNumber", $params['ID']);
        $order->setAttribute("Description", 'Оплата по счету ' . $params['ID']);
        $order->setAttribute("amount", $params['SummFull']);
        //$order->setAttribute("currency","");
        //$order->setAttribute("language","");
        //$order->setAttribute("pageView","MOBILE");
        $order->setAttribute("sessionTimeoutSecs", $params['TimeElapsed']);
        //$order->setAttribute("bindingId", "");

        $ans = $this->curlXmlReq($this->doc->saveXML(), $this->bankUrl);

        $ans['xml'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
 <soap:Body>
 <ns1:registerOrderResponse xmlns:ns1="http://engine.paymentgate.ru/webservices/merchant">
 <return orderId="05fcbc62-7ee6-4f1a-b3d5-6ca41a982283" errorCode="0" errorMessage="">
 <formUrl> https://server/application_context/mobile_payment_ru.html?mdOrder=05fcbc62-7ee6-4f1ab3d5-6ca41a982283 </formUrl>
 </return>
 </ns1:registerOrderResponse>
 </soap:Body>
 </soap:Envelope>';

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $return = $this->ParseResult($ans['xml'], 'registerOrderResponse');
            if ($return) {
                $error = $return->attributes->getNamedItem('errorCode')->nodeValue;
                if ($error == 0) {
                    $ordernumber = $return->attributes->getNamedItem('orderId')->nodeValue;
                    return ['status' => 1, 'transac' => $ordernumber];
                } else {
                    $message = $return->attributes->getNamedItem('errorMessage')->nodeValue;
                    return ['status' => 2, 'message' => $message, 'fatal' => 1];
                }
            } else {
                $fault = $this->ParseFault($ans['xml']);
                return ['status' => 2, 'message' => $fault->nodeValue, 'fatal' => 1];
            }
        }
        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    private function PayOrder(array $params, $ordernumber)
    {
        $body = $this->CreateSoap();
        $paymentOrder = $this->doc->createElementNS('http://engine.paymentgate.ru/webservices/merchant', 'mer:paymentOrder');
        $body->appendChild($paymentOrder);
        $order = $this->doc->createElement('order');
        $paymentOrder->appendChild($order);
        $order->setAttribute("orderId", $params['ID']);
        $order->setAttribute("pan", $params['card']['number']);
        $order->setAttribute("cvc", $params['card']['cvc']);
        $order->setAttribute("year", (int)("20" . $params['card']['year']));
        $order->setAttribute("month", (int)($params['card']['month']));
        $order->setAttribute("cardholderName", $params['card']['holder']);
        $order->setAttribute("language", "ru");
        $order->setAttribute("ip", $params['IPAddressUser']);
        //email
        //params

        //'Amount' => $params['SummFull'],
        //'Description' => 'Оплата по счету ' . $params['ID'],
        //'TTL' => '00.00:' . ($params['TimeElapsed'] / 60) . ':00'

        $ans = $this->curlXmlReq($this->doc->saveXML(), $this->bankUrl);

        $ans['xml'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
 <soap:Body>
 <ns1:paymentOrderResponse xmlns:ns1="http://engine.paymentgate.ru/webservices/merchant">
 <return errorCode="0" info=" , ..." redirect="https://test.paymentgate.ru:443/testpayment/rest
/finish3ds.do" acsUrl="https://test.paymentgate.ru/acs/auth/start.do" paReq="eJxVUk1zgjAQ
/SsM95KEr1pnjUOLnXqgYxUvvVHYAVQ+DFDUX99EUeshM/t2N2/3vQSmh2Kn
/aJo8qqc6MyguoZlXCV5mU70dfj+NNKnHMJMIPorjDuBHAJsmihFLU8metGkBtM5LLwl7jkMTFwSGSaQK5RXRJxFZcshivev809uWzazHCADhALF
3OfMtGzHfR4BuWAoowJ5iE27yqoayBlCXHVlK47ctS0gVwCd2PGsbesxIX3fG2lVpTs04qoAokpA7jssOhU1kuqQJzzwvf5yZqdPf0uDcHsM
/C8WnNIJENUBSdQiNykzGaWOxujYpmNqAznnISrUDtx1XUqloguCWg3xHkr/UyC9FNLqq4wrAjzUVYmyQ
/p3iyHBJr4ZodWDALmBygO5K3r7UB7HrXRvnXxvhdufxGKUdH34kiUb15mZ3k+3WSrnz01qXi79Yw67DFQAiKIhw6OS4cFl9PAR/gAOWr9V"/>
 </ns1:paymentOrderResponse>
 </soap:Body>
 </soap:Envelope>';

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $return = $this->ParseResult($ans['xml'], 'paymentOrderResponse');
            if ($return) {
                $error = $return->attributes->getNamedItem('errorCode')->nodeValue;
                if ($error == 0) {
                    $url = $return->attributes->getNamedItem('acsUrl')->nodeValue;
                    $pa = $return->attributes->getNamedItem('paReq')->nodeValue;
                    $md = md5($params['ID']);
                    return [
                        'status' => 1,
                        'transac' => $ordernumber,
                        'url' => $url,
                        'pa' => $pa,
                        'md' => $md
                    ];
                } else {
                    $message = $return->attributes->getNamedItem('errorMessage');
                    return ['status' => 2, 'message' => $message, 'fatal' => 1];
                }
            } else {
                $fault = $this->ParseFault($ans['xml']);
                return ['status' => 2, 'message' => $fault->nodeValue, 'fatal' => 1];
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
        $body = $this->CreateSoap();
        $paymentOrder = $this->doc->createElementNS('http://engine.paymentgate.ru/webservices/merchant', 'mer:getOrderStatusExtended');
        $body->appendChild($paymentOrder);
        $order = $this->doc->createElement('order');
        $paymentOrder->appendChild($order);
        ///$order->setAttribute("orderId", $params['ID']);
        $order->setAttribute("language", "ru");
        $paymentOrder->appendChild(
            $this->doc->createElement('merchantOrderNumber', $params['ID'])
        );

        $ans = $this->curlXmlReq($this->doc->saveXML(), $this->bankUrl);

        $ans['xml'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
<soap:Body> 
<ns1:getOrderStatusExtendedResponse xmlns:ns1="http://engine.paymentgate.ru/webservices/merchant">
<return orderNumber="0s7a84sPe49Hdsddd0134567a0" orderStatus="2" actionCode="0" actionCodeDescription="Request processed successfully" amount="33000" currency="643" date="2013-11-13T16:51:02.785+04:00" orderDescription=" " errorCode="0" errorMessage="Success"> 
<attributes name="mdOrder" value="942e8534-ac73-4e3c-96c6-f6cc448018f7"/> 
<cardAuthInfo maskedPan="411111**1111" expiration="201512" cardholderName="Ivan" approvalCode="123456"/> 
<authDateTime>2013-11-13T16:51:02.898+04:00</authDateTime> 
<terminalId>111113</terminalId> 
<authRefNum>111111111111</authRefNum> 
<paymentAmountInfo paymentState="DEPOSITED" approvedAmount="33000" depositedAmount="33000" refundedAmount="0"/> 
<bankInfo bankName="TEST CARD" bankCountryCode="RU" bankCountryName="Russian Federation"/> 
</return> 
</ns1:getOrderStatusExtendedResponse> 
</soap:Body> 
 </soap:Envelope>';

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $return = $this->ParseResult($ans['xml'], 'getOrderStatusExtendedResponse');
            if ($return) {
                $error = $return->attributes->getNamedItem('errorCode')->nodeValue;
                if ($error == 0) {
                    $orderStatus = $return->attributes->getNamedItem('orderStatus')->nodeValue;
                    $actionCodeDescription = $return->attributes->getNamedItem('actionCodeDescription')->nodeValue;
                    $cardAuthInfo = $this->GetChildNode($return, 'cardAuthInfo');
                    $bankInfo = $this->GetChildNode($return, 'bankInfo');
                    $status = $this->convertState($orderStatus);
                    return [
                        'state' => $status,
                        'xml' => [
                            'orderinfo' => [
                                'statedescription' => $actionCodeDescription
                            ],
                            'orderadditionalinfo' => [
                                'rrn' => '',
                                'cardnumber' => isset($cardAuthInfo) ? $cardAuthInfo->attributes->getNamedItem('maskedPan')->nodeValue : null
                            ]
                        ]
                    ];
                } elseif ($error == 6) {
                    //не найден в банке - если в кроне запрос, то отменить
                    if ($isCron && isset($params['IsCustom']) && TU::IsInPay($params['IsCustom'])) {
                        //не найден в банке - если в кроне запрос, то отменить
                        return ['state' => 2, 'xml' => ['orderinfo' => ['statedescription' => 'Платеж не проведен']]];
                    } else {
                        return ['state' => 0, 'xml' => ['orderinfo' => ['statedescription' => 'В обработке']]];
                    }
                } else {
                    $message = $return->attributes->getNamedItem('errorMessage');
                    return ['state' => 0, 'xml' => ['orderinfo' => ['statedescription' => $message]]];
                }
            } else {
                $fault = $this->ParseFault($ans['xml']);
                return ['state' => 0, 'xml' => ['orderinfo' => ['statedescription' =>  $fault->nodeValue]]];
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
     * @return DOMNode
     */
    private function CreateSoap()
    {
        $this->doc = new DOMDocument('1.0', 'utf-8');
        $envelope = $this->doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 's:Envelope');
        $this->doc->appendChild($envelope);
        $body = $this->doc->createElement('s:Body');
        $envelope->appendChild($body);
        return $body;
    }

    /**
     * Отправка POST запроса в банк
     * @param string $post
     * @param string $url
     * @param array $addHeader
     * @param bool $jsonReq
     * @return array [xml, error]
     */
    private function curlXmlReq($post, $url, $addHeader = [])
    {
        $timout = 110;
        $curl = new Curl();
        Yii::warning("req: login = " . $this->shopId . " url = " . $url . "\r\n" . Cards::MaskCardLog($post), 'merchant');
        try {
            $curl->reset()
                ->setOption(CURLOPT_TIMEOUT, $timout)
                ->setOption(CURLOPT_CONNECTTIMEOUT, $timout)
                ->setOption(CURLOPT_HTTPHEADER, array_merge([
                    'Content-Type: application/soap+xml; charset=utf-8',
                    'TCB-Header-Login: ' . $this->shopId,
                    'TCB-Header-Sign: ' . ''/*$this->HmacSha1($post, $this->keyFile)*/,
                    'TCB-Header-SerializerType: LowerCase'
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
                    $ans['xml'] = $this->ParseSoap($curl->response);
                    break;
                case 500:
                    $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
                    $ans['httperror'] = $this->ParseSoap($curl->response);
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

    private function ParseSoap($response)
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($response);
        return $doc->firstChild;
    }

    /**
     * @param $response
     * @param $node
     * @return DOMNode|\DOMNodeList|false|null
     */
    private function ParseResult($response, $node)
    {
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->loadXML($response);
        $xpath = new DOMXpath($xml);
        $return = $xpath->query("//*[local-name(.) = '".$node."']/return");
        $return = $return && $return->item(0) ? $return->item(0) : null;
        return $return;
    }

    /**
     * @param $response
     * @return DOMNode|\DOMNodeList|false|null
     */
    private function ParseFault($response)
    {
        $xpath = new DOMXpath($response);
        $fault = $xpath->query("//*[local-name(.) = 'registerOrderResponse']/fault");
        $fault = $fault && $fault->item(0) ? $fault->item(0) : null;
        return $fault;
    }

    private function GetChildNode(DOMNode $node, $nameChild)
    {
        $cnt = $node->childNodes->count();
        for ($i = 0; $i < $cnt; $i++) {
            $n = $node->childNodes->item($i);
            if ($n && $n->localName == $nameChild) {
                return $n;
            }
        }
        return null;
    }

}