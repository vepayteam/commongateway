<?php


namespace app\services\payment\forms\brs;


use app\services\payment\models\PartnerBankGate;

interface IXmlRequest
{
    /**
     * @param PartnerBankGate $partnerBankGate
     * @return string
     */
    public function buildXml(PartnerBankGate $partnerBankGate);

}
