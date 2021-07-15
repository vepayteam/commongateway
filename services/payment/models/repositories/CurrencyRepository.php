<?php

namespace app\services\payment\models\repositories;

use app\services\payment\models\Currency;
use yii\db\ActiveRecord;

class CurrencyRepository
{
    public static function getAll(): array
    {
        return Currency::find()->all();
    }
    /**
     * @param string $currency
     * @return bool
     */
    public function hasCurrency(string $currency): bool
    {
        return Currency::find()
            ->where([
                'Code' => $currency
            ])->exists();
    }

    /**
     * @param string $currency
     * @return array|ActiveRecord|null
     */
    public function getCurrency(string $currency)
    {
        return Currency::find()
            ->where([
                'Code' => $currency
            ])
            ->one();
    }

    /**
     * @return array|ActiveRecord|null
     */
    public function getDefaultMainCurrency()
    {
        return Currency::find()
            ->where([
                'Code' => 'RUB'
            ])->one();
    }
}