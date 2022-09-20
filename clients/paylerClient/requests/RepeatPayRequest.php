<?php

namespace app\clients\paylerClient\requests;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 * @property-read int $amount
 * @property-read string $recurrentTemplateId
 * @property-read string|null $currency
 */
class RepeatPayRequest extends ImmutableDataObject
{
    public function __construct(
        string $orderId,
        int $amount,
        string $recurrentTemplateId,
        ?string $currency
    )
    {
        parent::__construct(get_defined_vars());
    }
}