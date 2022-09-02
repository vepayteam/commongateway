<?php

namespace app\clients\paylerClient\requests;

use app\components\ImmutableDataObject;

/**
 * @property-read string $threeDsCompInd
 * @property-read string $threeDSServerTransId
 */
class ThreeDsMethodCompleteRequest extends ImmutableDataObject
{
    public function __construct(
        string $threeDsCompInd,
        string $threeDSServerTransId
    )
    {
        parent::__construct(get_defined_vars());
    }
}