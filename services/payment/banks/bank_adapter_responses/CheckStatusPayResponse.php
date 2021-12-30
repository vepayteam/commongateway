<?php

namespace app\services\payment\banks\bank_adapter_responses;

class CheckStatusPayResponse extends BaseResponse
{
    public $xml;
    public $rrn = '';
    public $transId;

    /**
     * @var string|null
     */
    public $rcCode;

    public $cardNumber;
    public $cardRefId;
    public $expYear;
    public $expMonth;
    public $cardHolder;
    public $operations;
}
