<?php

namespace app\helpers;

use Yii;

class UrlHelper
{
    public static function getApiUrl(): string
    {
        return str_replace(['http://', 'https://'], ['http://api.', 'https://api.'], Yii::$app->params['domain'] ?? '');
    }
}