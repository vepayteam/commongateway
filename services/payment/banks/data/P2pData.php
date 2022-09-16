<?php

namespace app\services\payment\banks\data;

use app\components\ImmutableDataObject;

/**
 * @property-read int $amountFractional
 * @property-read CurrencyData $currencyData
 * @property-read CardData $senderCardData
 * @property-read string $recipientCardPan
 */
final class P2pData extends ImmutableDataObject
{
    public function __construct(
        int $amountFractional,
        CurrencyData $currencyData,
        CardData $senderCardData,
        string $recipientCardPan
    )
    {
        parent::__construct(get_defined_vars());
    }
}