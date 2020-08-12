<?php


namespace app\models\bank\mts_soap;


use DOMDocument;
use SimpleXMLElement;
use Yii;
use yii\base\Model;
use GuzzleHttp\Client;

class SoapRequestBuilder extends Model
{
    private $url;
    private $method;
    /** @var Model */
    private $model;

    /** @var DOMDocument */
    private $dom;

    private $envelope;
    private $nodeHeader;
    private $nodeBody;

    /** @var SimpleXMLElement */
    private $xml;
    /** @var SimpleXMLElement */
    private $xmlHeader;
    /** @var SimpleXMLElement */
    private $xmlBody;


    public function __construct($url, $method, $model)
    {
        $this->url = $url;
        $this->model = $model;
        $this->method = $method;
        $this->createXml();
    }


    public function addSecurity($login, $password)
    {
        $securityNode = $this->dom->createElement("wsse:Security");
        $securityNode->setAttribute(
            'xmlns:wsse',
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'
        );
        $securityNode->setAttribute(
            'xmlns:wsu',
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-%20wssecurity-utility-1.0.xsd'
        );
        $this->nodeHeader->appendChild($securityNode);

        $usernameTokenNode = $this->dom->createElement("wsse:UsernameToken");
        $securityNode->appendChild($usernameTokenNode);

        $usernameTokenNode->appendChild($this->dom->createElement("wsse:Username", $login));
        $usernameTokenNode->appendChild($this->dom->createElement("wsse:Password", $password));
        return $this;
    }

    public function addBody()
    {
        $methodNode = $this->dom->createElement("p2p:".$this->method);
        $this->nodeBody->appendChild($methodNode);
        $argNode = $this->dom->createElement("arg0");
        $argNode->setAttribute('language', 'ru');
        $argNode->setAttribute('ip', Yii::$app->request->remoteIP);
        $argNode->setAttribute('type', $this->model->type);
        $methodNode->appendChild($argNode);

        foreach ($this->model->attributes as $k => $value) {
            if(!$value) {
                continue;
            }

            if(is_array($value)) {
                $node = $this->dom->createElement($k);
                $argNode->appendChild($node);
                foreach ($value as $kItem => $vItem) {
                    $elNode = $this->dom->createElement($kItem, $vItem);
                    $node->appendChild($elNode);
                }
            } else {
                $argNode->appendChild($this->dom->createElement($k, $value));
            }
        }
        return $this;
    }

    public function sendRequest()
    {
        $client = new Client();

        $response = $client->post($this->url, [
            'headers' => ['Content-Type' => 'text/xml; charset=UTF8'],
            'body' => $this->getXmlRaw(),
        ]);

        if($response->getStatusCode() != 200) {
            throw new \Exception('Ошибка запроса');
        }


        $xml = $response->getBody()->getContents();
        $xml = str_ireplace(['SOAP-ENV:', 'SOAP:', 'ns1:', 'xmlns:'], '', $xml);
        return simplexml_load_string($xml);
        
    }

    private function getXmlRaw()
    {
        $xml = $this->dom->saveXML($this->envelope);
        return $xml;
    }

    private function createXml()
    {
        $this->dom= new DOMDocument('1.0', 'utf-8');
        $this->envelope = $this->dom->createElement("soapenv:Envelope");
        $this->dom->appendChild($this->envelope);
        $this->envelope->setAttribute("xmlns:soapenv", "http://schemas.xmlsoap.org/soap/envelope/");
        $this->envelope->setAttribute("xmlns:p2p", "http://engine.paymentgate.ru/webservices/p2p");
        $this->nodeHeader = $this->dom->createElement("soapenv:Header");
        $this->envelope->appendChild($this->nodeHeader);
        $this->nodeBody = $this->dom->createElement("soapenv:Body");
        $this->envelope->appendChild($this->nodeBody);
    }




}
