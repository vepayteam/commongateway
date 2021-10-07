<?php

namespace app\services\logs\targets;

use app\helpers\Modifiers;
use yii\log\FileTarget;

class SecurityFileTarget extends FileTarget
{
    public function formatMessage($message): string
    {
        $message = parent::formatMessage($message);
        return Modifiers::searchAndReplaceSecurity($message);
    }
}
