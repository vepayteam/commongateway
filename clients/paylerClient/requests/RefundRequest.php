<?php

namespace app\clients\paylerClient\requests;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 * @property-read int $amount
 */
class RefundRequest extends ImmutableDataObject
{
    public function __construct(
        string $orderId,
        int $amount
    )
    {
        parent::__construct(get_defined_vars());
    }
}