<?php

namespace app\clients\payloniumClient\responses;

use yii\base\BaseObject;

class BalanceResponse extends BaseObject
{
    /**
     * @var float
     */
    private $amount;

    /**
     * @param float $amount
     */
    public function __construct(float $amount)
    {
        parent::__construct();

        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}