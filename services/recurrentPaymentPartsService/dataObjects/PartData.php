<?php

namespace app\services\recurrentPaymentPartsService\dataObjects;

class PartData
{
    /**
     * @var int
     */
    private $amountFractional;
    /**
     * @var int
     */
    private $partnerId;

    public function __construct(int $partnerId, int $amountFractional)
    {
        $this->partnerId = $partnerId;
        $this->amountFractional = $amountFractional;
    }

    /**
     * @return int
     */
    public function getPartnerId(): int
    {
        return $this->partnerId;
    }

    /**
     * @return int
     */
    public function getAmountFractional(): int
    {
        return $this->amountFractional;
    }
}