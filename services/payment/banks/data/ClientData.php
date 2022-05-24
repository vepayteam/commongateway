<?php

namespace app\services\payment\banks\data;

use app\components\ImmutableDataObject;
use yii\base\BaseObject;

/**
 * @property-read string $ip
 * @property-read string|null $headerUserAgent
 * @property-read string|null $headerAccept
 * @property-read int|null $browserScreenHeight Browser area height.
 * @property-read int|null $browserScreenWidth Browser area width.
 * @property-read int|null $browserTimezoneOffset Difference between browser timezone and UTC time in minutes. Example: -180.
 * @property-read int|null $browserWindowHeight
 * @property-read int|null $browserWindowWidth
 * @property-read int|null $browserLanguage
 * @property-read int|null $browserColorDepth Device color depth. Example: 24.
 * @property-read bool|null $browserJavaEnabled
 */
final class ClientData extends ImmutableDataObject
{
    public function __construct(
        string $ip,
        ?string $headerUserAgent,
        ?string $headerAccept,
        ?int $browserScreenHeight = null,
        ?int $browserScreenWidth = null,
        ?int $browserTimezoneOffset = null,
        ?int $browserWindowHeight = null,
        ?int $browserWindowWidth = null,
        ?string $browserLanguage = null,
        ?int $browserColorDepth = null,
        ?bool $browserJavaEnabled = null
    )
    {
        parent::__construct(get_defined_vars());
    }
}