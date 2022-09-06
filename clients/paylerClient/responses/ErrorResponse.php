<?php

namespace app\clients\paylerClient\responses;

use app\components\ImmutableDataObject;

/**
 * @property-read int $code
 * @property-read string $message
 */
class ErrorResponse extends ImmutableDataObject
{
    public function __construct(
        int $code,
        string $message
    )
    {
        parent::__construct(get_defined_vars());
    }
}