<?php

namespace app\services\payment\banks\bank_adapter_responses;

class GetBalanceResponse
{
    /** @var float */
    public $amount;
    /** @var string */
    public $currency;
    /** @var float */
    public $base_amount;
    /** @var float */
    public $rolling_reserve;
    /** @var float */
    public $base_rolling_reserve;
}
