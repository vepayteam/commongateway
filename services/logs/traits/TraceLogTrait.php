<?php

namespace app\services\logs\traits;

use app\helpers\EnvHelper;

trait TraceLogTrait
{
    private function formatMessagePrefix(): string
    {
        $traceId = EnvHelper::getParam(EnvHelper::UNIQUE_ID, '-');
        $paySchetId = EnvHelper::getParam(EnvHelper::PAYSCHET_ID, '-');
        $paySchetExtId = EnvHelper::getParam(EnvHelper::PAYSCHET_EXTID, '-');

        return '[' . $traceId . ']'
            . '[' . $paySchetId . ']'
            . '[' . $paySchetExtId . ']';
    }
}
