<?php


namespace app\services\payment\banks\bank_adapter_responses;


class OutCardPayResponse extends BaseResponse
{
    public $trans;
    /**
     * @var string Refresh status directive.
     */
    public $doRefreshStatus = false;
}
