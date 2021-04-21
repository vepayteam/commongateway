<?php

namespace app\services\balance\response;

class BalanceResponse
{
    public const BALANCE_UNAVAILABLE_ERROR_MSG = 'Сервис просмотра баланса временно не доступен';
    public const STATUS_ERROR = 0;
    public const STATUS_DONE = 1;

    /** @var int */
    public $status;
    /** @var string */
    public $message = '';
    /** @var array */
    public $balance;
    /** @var boolean */
    public $hasError = false;

    public function hasError(): bool
    {
        return $this->hasError;
    }

    public function getErrors(): string
    {
        return $this->message;
    }
}
