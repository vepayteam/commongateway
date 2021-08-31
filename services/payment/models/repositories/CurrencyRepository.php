<?php

namespace app\services\payment\models\repositories;

use app\services\payment\models\Currency;
use yii\db\ActiveRecord;
use yii\db\Query;

class CurrencyRepository
{
    /**
     * @return Currency[]
     */
    public static function getAll(): array
    {
        return Currency::find()->all();
    }

    /**
     * @param int $id
     * @return Currency|null
     */
    public static function getCurrencyCodeById(int $id): ?Currency
    {
        return Currency::findOne([
            'Id' => $id
        ]);
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
     * @param string|null $currencyCode
     * @param int|null $currencyId
     * @return null|Currency|Currency[]
     */
    public function getCurrency(string $currencyCode = null, int $currencyId = null)
    {
        $query = Currency::find();
        if (!$currencyCode && !$currencyId) {
            return $query->all();
        }
        if ($currencyCode) {
            $query->where([
                'Code' => $currencyCode
            ]);
        }
        if ($currencyId) {
            $query->where([
                'Id' => $currencyId
            ]);
        }
        return $query->one();
    }

    /**
     * @return array|ActiveRecord|null
     */
    public function getDefaultMainCurrency()
    {
        return Currency::find()
            ->where([
                'Code' => Currency::MAIN_CURRENCY
            ])->one();
    }
}
