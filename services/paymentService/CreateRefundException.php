<?php

namespace app\services\paymentService;

class CreateRefundException extends \Exception
{
    /** Refund amount exceeded. */
    public const AMOUNT_EXCEEDED = 1;
    /** Gate not found (in compensation calculations). */
    public const GATE_NOT_FOUND = 2;
    /** Compensation calculation error.  */
    public const COMPENSATION_ERROR = 3;
}