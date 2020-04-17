<?php

namespace app\models\bank;

use app\models\payonline\Cards;
use app\models\Payschets;
use DOMDocument;
use DOMNode;
use DOMXPath;
use qfsx\yii2\curl\Curl;
use Yii;

class MTSBank implements IBank
{
    public static $bank = 3;

    private $bankUrl = 'https://pay.mtsbank.ru';
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
            $this->bankUrl = 'https://test.paymentgate.ru:443';
        }

        if ($mtsGate) {
            $this->SetMfoGate($mtsGate->typeGate, $mtsGate->GetGates());
        }
    }

    public function SetMfoGate($type, $params)
    {

    }

    /**
     * @inheritDoc
     */
    public function confirmPay($idpay, $org = 0, $isCron = false)
    {
        $mesg = '';
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($idpay, null, $org);

        if ($params) {

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

    }

    /**
     * Возврат оплаты
     * @param int $IdPay
     * @return array
     * @throws \yii\db\Exception
     */
    public function reversOrder($IdPay)
    {

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

        $ans= '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
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
                $error = $return->attributes->getNamedItem('errorCode');
                if ($error == 0) {
                    $ordernumber = $return->attributes->getNamedItem('orderId');
                    return ['status' => 1, 'transac' => $ordernumber];
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

        $doc = $this->CreateSoap($mesg);

        $ans = $this->curlXmlReq($doc->saveXML(), $this->bankUrl);

        $ans= '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
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
                $error = $return->attributes->getNamedItem('errorCode');
                if ($error == 0) {
                    $url = $return->attributes->getNamedItem('acsUrl');
                    $pa = $return->attributes->getNamedItem('paReq');
                    $md = $return->attributes->getNamedItem('md');
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
}