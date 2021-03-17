<?php

namespace app\services\logs\targets;

use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\log\FileTarget;

class ReqMaskFileTarget extends FileTarget
{
    protected function getContextMessage()
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
            if (str_contains($dump, 'cardnum')) {
                $dump = $this->maskCardnum($dump);
            }

            $result[] = "\${$key} = " . $dump;
        }

        return implode("\n\n", $result);
    }

    private function maskCardnum($input) {
        $pattern = '/(\\\"cardnum\\\":.*?\\\"\d{4})(\d+?)(\d{4}\\\")/i';
        $replacement = '$1****$3';

        $replaced = preg_replace($pattern, $replacement, $input);
        if ($replaced) {
            return $replaced;
        }

        return $input;
    }
}
