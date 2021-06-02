<?php

namespace app\services\balance\response;

class BalanceResponse
{
    public const BALANCE_UNAVAILABLE_ERROR_MSG = 'Сервис просмотра баланса не доступен';
    public const PARTNER_NOT_FOUND_ERROR_MSG = 'Партнер не найден';
    public const STATUS_ERROR = 0;
    public const STATUS_DONE = 1;

    /** @var int */
    public $status;
    /** @var string */
    public $message = '';
    /** @var array */
    public $balance = [];
    /** @deprecated TODO: remove */
    public $amount = null;

    public function setBankBalance(array $balance)
    {
        $this->status = self::STATUS_DONE;
        $this->balance = $balance;
    }

    public function setError(string $message = '')
    {
        $this->status = self::STATUS_ERROR;
        $this->message = $message;
    }
}
