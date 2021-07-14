<?php

namespace app\services\logs\traits;

use app\services\Helpers;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

trait ReqMaskTargetTrait
{
    protected function getContextMessage(): string
    {
        $context = ArrayHelper::filter($GLOBALS, $this->logVars);
        foreach ($this->maskVars as $var) {
            if (ArrayHelper::getValue($context, $var) !== null) {
                ArrayHelper::setValue($context, $var, '***');
            }
        }
        $result = [];
        foreach ($context as $key => $value) {
            $dump = VarDumper::dumpAsString($value);
            $result[] = "\${$key} = " . Helpers::searchAndReplaceSecurity($dump);
        }
        return implode("\n\n", $result);
    }
}
