<?php

namespace app\assets;

use yii\web\AssetBundle;

class SwaggerAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        "swagger/swagger-ui.css",
    ];
    public $js = [
        "swagger/swagger-ui-bundle.js",
        "swagger/swagger-ui-standalone-preset.js"
    ];
}