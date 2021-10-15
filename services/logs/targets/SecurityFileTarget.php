<?php

namespace app\services\logs\targets;

use app\helpers\Modifiers;
use app\services\logs\traits\TraceLogTrait;
use yii\log\FileTarget;

class SecurityFileTarget extends FileTarget
{
    use TraceLogTrait;

    public function formatMessage($message): string
    {
        $message = parent::formatMessage($message);
        return Modifiers::searchAndReplaceSecurity($message);
    }

    /**
     * @inheritdoc
     */
    public function getMessagePrefix($message): string
    {
        $parentPrefix = parent::getMessagePrefix($message);
        $tracePrefix = $this->getTracePrefix();

        return $tracePrefix . $parentPrefix;
    }
}
