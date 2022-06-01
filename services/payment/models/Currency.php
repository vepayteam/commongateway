<?php

namespace app\services\payment\models;

use app\services\payment\models\repositories\CurrencyRepository;
use yii\db\ActiveRecord;

/**
 * @property string Name
 * @property int Number
 * @property string Code
 * @property string Symbol
 * @property int Id
 */
class Currency extends ActiveRecord
{
    public const RUB = 'RUB';
    public const USD = 'USD';
    public const EUR = 'EUR';
    public const AZN = 'AZN';
    public const SYMBOLS = [
        self::RUB => '₽',
        self::USD => '$',
        self::EUR => '€',
        self::AZN => '₼',
    ];
    public const MAIN_CURRENCY = self::RUB;

    public static function tableName(): string
    {
        return 'currency';
    }

    public function rules(): array
    {
        return [
            [['Code'], 'required'],
            [['Number'], 'integer'],
            [['Code', 'Symbol'], 'string', 'max' => 3],
            [['Name'], 'string', 'max' => 250],
        ];
    }

    public static function getCurrencyCodes(): array
    {
        return array_keys(CurrencyRepository::getCurrenciesByCode());
    }

    public function getSymbol(): ?string
    {
        return self::SYMBOLS[$this->Code] ?? null;
    }
}