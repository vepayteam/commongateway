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
     * @var Currency[]
     */
    private static $currenciesById;
    /**
     * @var Currency[]
     */
    private static $currenciesByCode;

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
        if (!self::$currenciesById) {
            self::$currenciesById = ArrayHelper::index(self::getCurrencies(), 'Id');
        }
        return self::$currenciesById;
    }

    /**
     * @return Currency[]
     */
    public static function getCurrenciesByCode(): array
    {
        if (!self::$currenciesByCode) {
            self::$currenciesByCode = ArrayHelper::index(self::getCurrencies(), 'Code');
        }
        return self::$currenciesByCode;
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
        return self::getCurrenciesById();
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
            return self::getCurrenciesById();
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
