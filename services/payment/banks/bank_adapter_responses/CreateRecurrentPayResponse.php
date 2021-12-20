<?php


namespace app\services\payment\banks\bank_adapter_responses;


class CreateRecurrentPayResponse extends BaseResponse
{
    public $rrn;
    public $transac;

    /**
     * @var int|null Interval in seconds between status refresh requests for recurrent payments.
     */
    public $refreshStatusInterval = null;
}
