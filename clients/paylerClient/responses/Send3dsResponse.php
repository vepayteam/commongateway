<?php

namespace app\clients\paylerClient\responses;

use app\components\ImmutableDataObject;

/**
 * @property-read int $authType
 * @property-read int $amount
 * @property-read string|null $recurrentTemplateId
 * @property-read string $orderId
 * @property-read string|null $status
 */
class Send3dsResponse extends ImmutableDataObject
{
    public function __construct(
        int $authType,
        int $amount,
        ?string $recurrentTemplateId,
        string $orderId,
        ?string $status
    )
    {
        parent::__construct(get_defined_vars());
    }
}