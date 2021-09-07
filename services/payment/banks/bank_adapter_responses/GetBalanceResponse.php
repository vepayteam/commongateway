<?php

namespace app\services\payment\banks\bank_adapter_responses;

class GetBalanceResponse
{
    /** @var string */
    public $bank_name = "";
    /** @var float */
    public $amount;
    /** @var string */
    public $currency;
    /** @var int */
    public $account_type;

    public function __toString()
    {
        return "$this->bank_name:$this->amount:$this->currency:$this->account_type";
    }
}
