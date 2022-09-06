<?php

namespace app\services\payToCardService;

class CreatePaymentException extends \Exception
{
    public const NO_USLUGATOVAR = 1;
    public const NO_GATE = 2;
    public const TOKEN_ERROR = 3;
}