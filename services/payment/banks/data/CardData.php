<?php

namespace app\services\payment\banks\data;

use app\components\ImmutableDataObject;

/**
 * @property-read string $pan
 * @property-read int $expYear
 * @property-read int $expMonth
 * @property-read int $cvv
 * @property-read string $cardHolder
 */
final class CardData extends ImmutableDataObject
{
    public function __construct(
        string $pan,
        int $expYear,
        int $expMonth,
        int $cvv,
        string $cardHolder
    )
    {
        parent::__construct(get_defined_vars());
    }
}