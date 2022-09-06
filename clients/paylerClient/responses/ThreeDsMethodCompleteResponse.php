<?php

namespace app\clients\paylerClient\responses;

use app\components\ImmutableDataObject;

/**
 * @property-read string|null $acsUrl
 * @property-read string|null $cReq
 * @property-read int $authType
 * @property-read int $amount
 * @property-read string|null $recurrentTemplateId
 * @property-read string|null $cardNumber
 * @property-read string|null $cardHolder
 * @property-read int|null $expiredYear
 * @property-read int|null $expiredMonth
 * @property-read string $orderId
 * @property-read string|null $status
 */
class ThreeDsMethodCompleteResponse extends ImmutableDataObject
{
    public function __construct(
        ?string $acsUrl,
        ?string $cReq,
        int $authType,
        int $amount,
        ?string $recurrentTemplateId,
        ?string $cardNumber,
        ?string $cardHolder,
        ?int $expiredYear,
        ?int $expiredMonth,
        string $orderId,
        ?string $status
    )
    {
        parent::__construct(get_defined_vars());
    }
}