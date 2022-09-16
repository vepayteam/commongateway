<?php

namespace app\clients\paylerClient\responses;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 * @property-read int $amount
 * @property-read string $status
 * @property-read string|null $recurrentTemplateId
 * @property-read string|null $paymentType
 */
class GetStatusResponse extends ImmutableDataObject
{
    public function __construct(
        string $orderId,
        int $amount,
        string $status,
        ?string $recurrentTemplateId,
        ?string $paymentType
    )
    {
        parent::__construct(get_defined_vars());
    }
}