<?php

namespace app\clients\paylerClient\requests;

use app\components\ImmutableDataObject;

/**
 * @property-read string $paRes
 * @property-read string $md
 */
class Send3dsRequest extends ImmutableDataObject
{
    public function __construct(
        string $paRes,
        string $md
    )
    {
        parent::__construct(get_defined_vars());
    }
}