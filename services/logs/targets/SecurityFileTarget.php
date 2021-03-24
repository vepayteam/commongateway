<?php

namespace app\services\logs\targets;

use app\services\logs\traits\SecurityTargetTrait;
use yii\log\FileTarget;

class SecurityFileTarget extends FileTarget
{
    use SecurityTargetTrait;

    public function dump($log)
    {
        file_put_contents($this->logFile, $log, FILE_APPEND | LOCK_EX);
    }

    public function formatMsg($format, $args): string
    {
        return sprintf($format, ...$args);
    }
}
