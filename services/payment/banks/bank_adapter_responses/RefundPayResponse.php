<?php

namespace app\services\payment\banks\bank_adapter_responses;

use app\services\base\traits\Fillable;

class RefundPayResponse extends BaseResponse
{
    use Fillable;

    public $state;
}
