<?php

namespace app\services\payment\types;

use yii\base\Model;

/**
 * Типы банковских счетов (SchetTypes) - транзитный | номинальный
 * для определения по какому счету вывод баланса (только для ТКБ)
 * Class AccountTypes
 */
abstract class AccountTypes extends Model
{
    public const TYPE_DEFAULT = 0;
    public const TYPE_TRANSIT = 1;
    public const TYPE_NOMINAL = 2;

    public const ALL_TYPES = [
        self::TYPE_DEFAULT => '',
        self::TYPE_TRANSIT => 'Транзитный',
        self::TYPE_NOMINAL => 'Номинальный',
    ];
}
