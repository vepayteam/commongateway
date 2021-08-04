<?php

namespace app\services\logs\targets;

use app\helpers\EnvHelper;
use app\helpers\Modifiers;
use yii\log\FileTarget;

class SecurityFileTarget extends FileTarget
{
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
        $traceId = EnvHelper::getParam(EnvHelper::UNIQUE_ID, '-');
        $paySchetId = EnvHelper::getParam(EnvHelper::PAYSCHET_ID, '-');
        $paySchetExtId = EnvHelper::getParam(EnvHelper::PAYSCHET_EXTID, '-');

        return '[' . $traceId . ']'
            . '[' . $paySchetId . ']'
            . '[' . $paySchetExtId . ']';
    }
}
