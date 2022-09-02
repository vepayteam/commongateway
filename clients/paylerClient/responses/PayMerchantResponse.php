<?php

namespace app\clients\paylerClient\responses;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 * @property-read int $amount
 * @property-read int $authType
 * @property-read string|null $recurrentTemplateId
 * @property-read string|null $cardId
 * @property-read string|null $cardStatus
 * @property-read string|null $cardNumber
 * @property-read string|null $cardHolder
 * @property-read int|null $expiredMonth
 * @property-read int|null $expiredYear
 * @property-read string|null $acsUrl
 * @property-read string|null $md
 * @property-read string|null $paReq
 * @property-read string|null $threeDSServerTransID
 * @property-read string|null $threeDSMethodUrl
 * @property-read string|null $cReq
 * @property-read string|null $status
 */
class PayMerchantResponse extends ImmutableDataObject
{
    public function __construct(
        string $orderId,
        int $amount,
        int $authType,
        ?string $recurrentTemplateId,
        ?string $cardId,
        ?string $cardStatus,
        ?string $cardNumber,
        ?string $cardHolder,
        ?int $expiredMonth,
        ?int $expiredYear,
        ?string $acsUrl,
        ?string $md,
        ?string $paReq,
        ?string $threeDSServerTransID,
        ?string $threeDSMethodUrl,
        ?string $cReq,
        ?string $status
    )
    {
        parent::__construct(get_defined_vars());
    }
}