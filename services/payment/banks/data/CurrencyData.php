<?php

namespace app\services\payment\banks\data;

use app\components\ImmutableDataObject;
use app\services\payment\models\Currency;

/**
 * @property-read string $code ISO 4217 currency code.
 * @property-read int $number ISO 4217 currency number.
 */
final class CurrencyData extends ImmutableDataObject
{
    public function __construct(string $code, int $number)
    {
        parent::__construct(get_defined_vars());
    }

    public static function fromCurrency(Currency $currency): CurrencyData
    {
        return new CurrencyData($currency->Code, $currency->Number);
    }
}