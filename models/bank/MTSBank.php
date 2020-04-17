<?php

namespace app\models\bank;

use app\models\payonline\Cards;
use app\models\Payschets;
use DOMDocument;
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
    private $backUrls = ['ok' => 'https://api.vepay.online/merchant/orderok?orderid='];

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
            $this->bankUrl = 'https://paytest.online.tkbbank.ru';
        }

        if ($mtsGate) {
            $this->SetMfoGate($mtsGate->typeGate, $mtsGate->GetGates());
        }
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
        $this->RegisterOrder($params);
        $this->PayOrder($params);
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
        $mesg = new DOMDocument('1.0', 'utf-8');
        $paymentOrder = $mesg->createElementNS('http://engine.paymentgate.ru/webservices/merchant', 'mer:registerOrder');
        $mesg->appendChild($paymentOrder);
        $order = $mesg->createElement('order');
        $paymentOrder->appendChild($order);
        $order->setAttribute("merchantOrderNumber", $params['ID']);
        $order->setAttribute("Description", 'Оплата по счету ' . $params['ID']);
        $order->setAttribute("amount", $params['SummFull']);
        //$order->setAttribute("currency","");
        //$order->setAttribute("language","");
        //$order->setAttribute("pageView","MOBILE");
        $order->setAttribute("sessionTimeoutSecs", $params['TimeElapsed']);
        //$order->setAttribute("bindingId", "");

        $doc = $this->CreateSoap($mesg);

        $ans = $this->curlXmlReq($doc->saveXML(), $this->bankUrl);
        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xml = $ans['xml'];
            if (isset($xml['Status']) && $xml['Status'] == '0') {
            } else {
                return ['status' => 2, 'message' => $xml['errorinfo']['errormessage']];
            }
        }
        return ['status' => 0, 'message' => 'Ошибка запроса, попробуйте повторить позднее', 'fatal' => 0];
    }

    private function PayOrder(array $params)
    {
        $mesg = new DOMDocument('1.0', 'utf-8');
        $paymentOrder = $mesg->createElementNS('http://engine.paymentgate.ru/webservices/merchant', 'mer:paymentOrder');
        $mesg->appendChild($paymentOrder);
        $order = $mesg->createElement('order');
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
        $doc->saveXML();

    }

    /**
     * @param DOMDocument $mesg
     * @return DOMDocument
     */
    private function CreateSoap(DOMDocument $mesg)
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $envelope = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 's:Envelope');
        $body = $doc->createElement('s:Body');
        $envelope->appendChild($body);
        $body->appendChild($mesg->firstChild);
        return $doc;
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

}