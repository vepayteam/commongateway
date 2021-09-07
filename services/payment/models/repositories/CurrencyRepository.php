<?php

namespace app\services\payment\models\repositories;

use app\services\payment\models\Currency;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * CurrencyRepository
 */
class CurrencyRepository
{
    /**
     * @var Currency[]
     */
    private static $currencies;

    /**
     * @return Currency[]
     */
    public static function getCurrencies(): array
    {
        if (!self::$currencies) {
            self::$currencies = Currency::find()->all();
        }
        return self::$currencies;
    }

    /**
     * @return Currency[]
     */
    public static function getCurrenciesById(): array
    {
        return ArrayHelper::index(self::getCurrencies(), 'Id');
    }

    /**
     * @return Currency[]
     */
    public static function getCurrenciesByCode(): array
    {
        return ArrayHelper::index(self::getCurrencies(), 'Code');
    }

    public static function getCurrencyById(int $id): ?Currency
    {
        return self::getCurrenciesById()[$id] ?? null;
    }

    public static function getCurrencyByCode(string $code): ?Currency
    {
        return self::getCurrenciesByCode()[$code] ?? null;
    }

    /**
     * @return Currency[]
     */
    public static function getAll(): array
    {
        return self::getCurrencies();
    }

    public static function getCurrencyCodeById(int $id): ?Currency
    {
        return self::getCurrencyById($id);
    }

    public function hasCurrency(string $code): bool
    {
        return (bool)self::getCurrencyByCode($code);
    }

    /**
     * @param string|null $code
     * @param int|null $id
     *
     * @return null|Currency|Currency[]
     */
    public function getCurrency(string $code = null, int $id = null)
    {
        if (!$code && !$id) {
            return self::getCurrencies();
        }
        $code = $code ? self::getCurrencyByCode($code) : $code;
        $id = $id ? self::getCurrencyById($id) : $id;

        return $code == $id ? $id : null;
    }

    public function getDefaultMainCurrency(): ?Currency
    {
        return self::getCurrencyByCode(Currency::MAIN_CURRENCY);
    }
}
