<?php

namespace app\clients\payloniumClient\requests;

class BalanceRequest extends BaseRequest
{
    /**
     * @inheritdoc
     */
    public function toRequestString(): string
    {
        $xmlRequest = $this->getCommonRequestXmlElement();
        $xmlRequest->addChild('balance');

        return $this->convertXmlElementToString($xmlRequest);
    }
}