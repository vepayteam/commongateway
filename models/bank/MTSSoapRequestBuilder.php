<?php


namespace app\models\bank;


class MTSSoapRequestBuilder
{
    private $method;

    private $xml;

    public function __construct($method)
    {
        $this->method = $method;
    }

    public function addSecurity($login, $password)
    {


    }

    private function createXml()
    {
        $this->xml = new \SimpleXMLElement('<soapenv:Envelope/>');
        $this->xml->addAttribute('xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $this->xml->addAttribute('xmlns:p2p', 'http://engine.paymentgate.ru/webservices/p2p');
    }

}
