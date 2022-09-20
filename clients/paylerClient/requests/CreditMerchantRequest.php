<?php

namespace app\clients\paylerClient\requests;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 * @property-read string $cardNumber
 * @property-read int $amount
 * @property-read string|null $email
 * @property-read string|null $currency
 * @property-read string|null $cardHolder
 * @property-read string|null $lang
 */
class CreditMerchantRequest extends ImmutableDataObject
{
    public function __construct(
        string $orderId,
        string $cardNumber,
        int $amount,
        ?string $email,
        ?string $currency = null,
        ?string $cardHolder = null,
        ?string $lang = null
    )
    {
        parent::__construct(get_defined_vars());
    }
}