<?php

namespace app\helpers;

class EnvHelper
{
    public const UNIQUE_ID = 'UNIQUE_ID';
    public const PAYSCHET_ID = 'PAYSCHET_ID';
    public const PAYSCHET_EXTID = 'PAYSCHET_EXTID';

    public static function getParam($name, $default = null)
    {
        return getenv($name) ?: $default;
    }

    public static function setParam($name, $value)
    {
        putenv($name . '=' . $value);
    }

    public static function getTraceParams(): array
    {
        $traceId = EnvHelper::getParam(EnvHelper::UNIQUE_ID, '-');
        $paySchetId = EnvHelper::getParam(EnvHelper::PAYSCHET_ID, '-');
        $paySchetExtId = EnvHelper::getParam(EnvHelper::PAYSCHET_EXTID, '-');

        return [$traceId, $paySchetId, $paySchetExtId];
    }
}