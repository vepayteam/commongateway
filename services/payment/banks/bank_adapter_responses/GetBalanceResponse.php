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
    public $accountType;
}
