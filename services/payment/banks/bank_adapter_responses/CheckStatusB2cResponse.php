<?php

namespace app\services\payment\banks\bank_adapter_responses;

/**
 * Class CheckStatusB2cResponse
 */
class CheckStatusB2cResponse extends BaseResponse
{
    /**
     * @var int $status
     */
    public $status;
    /**
     * @var string $message
     */
    public $message;
}
