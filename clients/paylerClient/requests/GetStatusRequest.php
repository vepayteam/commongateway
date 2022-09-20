<?php

namespace app\clients\paylerClient\requests;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 */
class GetStatusRequest extends ImmutableDataObject
{
    public function __construct(string $orderId)
    {
        parent::__construct(get_defined_vars());
    }
}