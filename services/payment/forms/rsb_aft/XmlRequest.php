<?php


namespace app\services\payment\forms\rsb_aft;


interface XmlRequest
{
    /**
     * @return string
     */
    public function buildXml(string $privateKeyPath);

}
