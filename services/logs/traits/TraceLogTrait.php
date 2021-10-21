<?php

namespace app\services\logs\traits;

use app\helpers\EnvHelper;

trait TraceLogTrait
{
    public function getTracePrefix(): string
    {
        [$traceId, $paySchetId, $paySchetExtId] = EnvHelper::getTraceParams();

        return '[' . $traceId . ']'
            . '[' . $paySchetId . ']'
            . '[' . $paySchetExtId . ']';
    }
}