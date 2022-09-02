<?php

namespace app\clients\paylerClient\responses;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 * @property-read int $amount
 * @property-read string|null $cardHolder
 * @property-read string|null $cardNumber
 * @property-read string $status
 */
class CreditMerchantResponse extends ImmutableDataObject
{
    public function __construct(
        string $orderId,
        int $amount,
        ?string $cardHolder,
        ?string $cardNumber,
        string $status
    )
    {
        parent::__construct(get_defined_vars());
    }
}