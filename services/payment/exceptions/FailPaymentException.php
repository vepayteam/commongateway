<?php

namespace app\services\payment\exceptions;

/**
 * The exception thrown when payment failed, therefore a redirect to the fail URL must be done.
 */
class FailPaymentException extends \Exception
{
}