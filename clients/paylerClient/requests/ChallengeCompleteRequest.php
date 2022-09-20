<?php

namespace app\clients\paylerClient\requests;

use app\components\ImmutableDataObject;

/**
 * @property-read string $cRes
 */
class ChallengeCompleteRequest extends ImmutableDataObject
{
    public function __construct(
        string $cRes
    )
    {
        parent::__construct(get_defined_vars());
    }
}