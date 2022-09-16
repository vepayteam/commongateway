<?php

namespace app\clients\paylerClient\responses;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 * @property-read int $amount
 * @property-read string $status
 */
class RepeatPayResponse extends ImmutableDataObject
{
    public function __construct(
        string $orderId,
        int $amount,
        string $status
    )
    {
        parent::__construct(get_defined_vars());
    }
}