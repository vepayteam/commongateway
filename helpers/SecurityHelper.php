<?php

namespace app\helpers;

class SecurityHelper
{
    public static function generateUuid(): string
    {
        try {
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }
}