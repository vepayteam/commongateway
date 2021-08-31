<?php

namespace app\services\exchange_rates\models;

use yii\base\Model;

class ExchangeRateUpdateResult extends Model
{
    const STATUS_ERROR = 1;
    const STATUS_DONE = 2;

    /**
     * @var int
     */
    public $status;

    /**
     * @var string
     */
    public $error;

    /**
     * Количество курсов, добавленных в базу
     * @var int
     */
    public $inserted;

    /**
     * Количество полученных курсов
     * @var int
     */
    public $rateCount;

    public static function setError(string $error): ExchangeRateUpdateResult
    {
        $result = new ExchangeRateUpdateResult();
        $result->status = self::STATUS_ERROR;
        $result->error = $error;

        return $result;
    }

    public static function setDone(int $inserted, int $rateCount): ExchangeRateUpdateResult
    {
        $result = new ExchangeRateUpdateResult();
        $result->status = self::STATUS_DONE;
        $result->inserted = $inserted;
        $result->rateCount = $rateCount;

        return $result;
    }
}
