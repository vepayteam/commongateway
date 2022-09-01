<?php

namespace app\services\payToCardService\data;

use app\components\ImmutableDataObject;
use app\models\payonline\Cards;
use app\services\payment\models\Currency;

/**
 * @property-read int $amountFractional
 * @property-read Currency $currency
 * @property-read string $documentId
 * @property-read string $fullName
 * @property-read string $extId
 * @property-read string $timeout
 * @property-read string $successUrl
 * @property-read string $failUrl
 * @property-read string $cancelUrl
 * @property-read string $language
 * @property-read string $recipientCardNumber
 * @property-read Cards|null $presetSenderCard
 * @property-read string|null $postbackUrl
 * @property-read string|null $postbackUrlV2
 * @property-read bool $cardRegistration
 * @property-read string|null $description
 */
final class CreatePaymentData extends ImmutableDataObject
{
    public function __construct(
        int $amountFractional,
        Currency $currency,
        ?string $documentId,
        ?string $fullName,
        ?string $extId,
        string $timeout,
        ?string $successUrl,
        ?string $failUrl,
        ?string $cancelUrl,
        ?string $language,
        string $recipientCardNumber,
        ?Cards $presetSenderCard,
        ?string $postbackUrl,
        ?string $postbackUrlV2,
        bool $cardRegistration,
        ?string $description
    )
    {
        parent::__construct(get_defined_vars());
    }
}