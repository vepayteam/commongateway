<?php


namespace app\services\payment\forms\brs;


use app\services\payment\banks\BRSAdapter;
use app\services\payment\models\PartnerBankGate;
use Yii;
use yii\base\Model;

class XmlRequest
{
    private $attributes;
    /** @var PartnerBankGate */
    private $partnerBankGate;

    public function __construct(Model $model, PartnerBankGate $partnerBankGate)
    {
        $this->attributes = $model->getAttributes();
        $this->partnerBankGate = $partnerBankGate;
    }

    /**
     * @return string
     */
    public function buildXml()
    {
        $xmlBody = $this->buildBody();
        $signature = $this->buildSignature($xmlBody);
        $xmlTemplate = '<?xml version="1.0" encoding="windows-1251"?>
        <rsb_ns:gateway xmlns:rsb_ns="http://dit.rsb.ru/gateway/request">
          <rsb_ns:request>%s</rsb_ns:request>
         <rsb_ns:sig>%s</rsb_ns:sig>
         </rsb_ns:gateway> 
        ';
        // $xmlTemplate = iconv('UTF-8', 'WINDOWS-1251', $xmlTemplate);
        $xml = sprintf($xmlTemplate, $xmlBody, $signature);
        return $xml;
    }

    /**
     * @return string
     */
    protected function buildBody()
    {
        $xml = '';
        foreach ($this->attributes as $name => $value) {
            $xmlField = sprintf('<rsb_ns:%1$s>%2$s</rsb_ns:%1$s>', $name, $value);
            $xml .= $xmlField;
        }
        return $xml;
    }

    /**
     * @param string $xml
     * @return string
     */
    protected function buildSignature(string $xml)
    {
        $hash = sha1($xml);
        $signature = '';
        $privateKey = file_get_contents(Yii::getAlias(BRSAdapter::KEYS_PATH . $this->partnerBankGate->Login . '.key'));
        openssl_private_encrypt($hash, $signature, $privateKey);
        return bin2hex($signature);
    }
}
