<?php

namespace app\services\payment\exceptions;

class BankAdapterResponseException extends \Exception
{
    public const REQUEST_ERROR_MSG = 'Ошибка запроса';

    public static function setErrorMsg($msg): string
    {
        return self::REQUEST_ERROR_MSG . ': ' . $msg;
    }
}
