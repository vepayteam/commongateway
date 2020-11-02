<?php


namespace app\services\payment\banks\bank_adapter_responses;

class Check3DSVersionResponse extends BaseResponse
{
    public $version;
    public $transactionId;
    public $url;
}
