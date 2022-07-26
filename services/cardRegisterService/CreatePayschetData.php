<?php

namespace app\services\cardRegisterService;

use app\services\LanguageService;

interface CreatePayschetData
{
    public const TYPE_PAYMENT = 0;
    public const TYPE_OUT = 1;

    /**
     * Registration process: pay or out. See TYPE_* constants.
     */
    public function getType(): int;

    public function getExtId(): ?string;

    public function getSuccessUrl(): ?string;

    public function getFailUrl(): ?string;

    public function getCancelUrl(): ?string;

    public function getPostbackUrl(): ?string;

    public function getPostbackUrlV2(): ?string;

    /**
     * One of {@see LanguageService::ALL_API_LANG_LIST}.
     *
     * @return string
     */
    public function getLanguage(): ?string;
}