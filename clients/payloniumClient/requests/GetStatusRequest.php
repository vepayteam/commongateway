<?php

namespace app\clients\payloniumClient\requests;

class GetStatusRequest extends BaseRequest
{
    /**
     * @var int
     */
    public $paymentId;

    /**
     * @param int $paymentId
     */
    public function __construct(int $paymentId)
    {
        parent::__construct();

        $this->paymentId = $paymentId;
    }

    /**
     * @inheritdoc
     */
    public function toRequestString(): string
    {
        $xmlRequest = $this->getCommonRequestXmlElement();
        $xmlRequest->addChild('status');
        $xmlRequest->status->addAttribute('id', $this->paymentId);

        return $this->convertXmlElementToString($xmlRequest);
    }
}