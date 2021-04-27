<?php

namespace app\services\payment\banks\bank_adapter_responses;

class GetBalanceResponse
{
    /** @var string */
    public $bank_name = "";
    /** @var array */
    public $balance = [];
}
