<?php

namespace app\Api\Payment\Cauri\Responses;

use app\services\payment\banks\bank_adapter_responses\BaseResponse;

class TransactionStatusResponse extends BaseResponse
{
    public $id;
    public $originalStatus = '';
    public $userId = null;
}
