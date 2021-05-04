<?php

namespace app\Api\Payment\Cauri\Responses;

use app\services\payment\banks\bank_adapter_responses\BaseResponse;

class TransactionStatusResponse extends BaseResponse
{
    /** @var int */
    public $id;
    /** @var string */
    public $originalStatus = '';
    /** @var int */
    public $userId = null;
    /** @var array */
    public $card = [];
}
