<?php

namespace app\services\payment\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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

    public const SYMBOLS = [
        self::RUB => '₽',
        self::USD => '$',
        self::EUR => '€'
    ];

    public const MAIN_CURRENCY = 'RUB';

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

    /**
     * @return array
     */
    public static function getCurrencyCodes(): array
    {
        $currencies = Currency::find()
            ->select(['Code'])
            ->all();

        return ArrayHelper::getColumn($currencies, 'Code');
    }

    public function getSymbol(): ?string
    {
        return self::SYMBOLS[$this->Code] ?? null;
    }
}
