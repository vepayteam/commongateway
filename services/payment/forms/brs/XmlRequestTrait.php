<?php


namespace app\services\payment\forms\brs;


use app\services\payment\banks\BRSAdapter;
use app\services\payment\models\PartnerBankGate;
use Yii;

trait XmlRequestTrait
{
    /**
     * @return string
     */
    public function buildXml(PartnerBankGate $partnerBankGate)
    {
        $xmlBody = $this->buildBody();
        $signature = $this->buildSignature($xmlBody, $partnerBankGate);
        $xmlTemplate = '<?xml version="1.0" encoding="windows-1251"?>
        <rsb_ns:gateway xmlns:rsb_ns="http://dit.rsb.ru/gateway/request"><rsb_ns:request>%s</rsb_ns:request><rsb_ns:sig>%s</rsb_ns:sig></rsb_ns:gateway>';
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
     * @param string $body
     * @return string
     */
    protected function buildSignature(string $body, PartnerBankGate $partnerBankGate)
    {
        if(!file_exists(Yii::getAlias('@runtime/requests'))) {
            mkdir(Yii::getAlias('@runtime/requests'), 0777);
        }

        $fileRequest = Yii::getAlias('@runtime/requests/' . Yii::$app->security->generateRandomString(32) . '.txt');
        $fileResponse = Yii::getAlias('@runtime/requests/' . Yii::$app->security->generateRandomString(32) . '.txt');
        file_put_contents($fileRequest, $body);

        $cmd  = sprintf('openssl dgst -sha1 -sign "%s" -hex "%s" > "%s"',
            Yii::getAlias(BRSAdapter::KEYS_PATH . $partnerBankGate->Login . '.key'),
            $fileRequest,
            $fileResponse
        );
        shell_exec($cmd);

        $signature = file_get_contents($fileResponse);
        $signature = explode('=', $signature)[1];
        $signature = trim($signature);

        unlink($fileRequest);
        unlink($fileResponse);
        return $signature;
    }

}
