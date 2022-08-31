<?php

namespace app\clients\paylerClient\requests;

use app\components\ImmutableDataObject;

/**
 * @property-read string $orderId
 * @property-read string $currency
 * @property-read int $amount
 * @property-read string $cardNumber
 * @property-read string $cardHolder
 * @property-read int $expiredYear
 * @property-read int $expiredMonth
 * @property-read string $secureCode
 * @property-read string|null $email
 * @property-read string $payerIp
 * @property-read string $browserAccept
 * @property-read string $browserLanguage
 * @property-read string $browserUserAgent
 * @property-read bool $browserJavaEnabled
 * @property-read bool $browserJavaScriptEnabled
 * @property-read string $browserScreenHeight
 * @property-read string $browserScreenWidth
 * @property-read string $browserColorDepth
 * @property-read string $browserTZ
 * @property-read string $threeDsNotificationUrl
 * @property-read int|null $recurrent
 * @property-read string|null $lang
 * @property-read string|null $userData
 */
class PayMerchantRequest extends ImmutableDataObject
{
    public function __construct(
        string $orderId,
        string $currency,
        int $amount,
        string $cardNumber,
        string $cardHolder,
        int $expiredYear,
        int $expiredMonth,
        string $secureCode,
        ?string $email,
        string $payerIp,
        string $browserAccept,
        string $browserLanguage,
        string $browserUserAgent,
        bool $browserJavaEnabled,
        bool $browserJavaScriptEnabled,
        string $browserScreenHeight,
        string $browserScreenWidth,
        string $browserColorDepth,
        string $browserTZ,
        string $threeDsNotificationUrl,
        ?int $recurrent = null,
        ?string $lang = null,
        ?string $userData = null
    )
    {
        parent::__construct(get_defined_vars());
    }
}