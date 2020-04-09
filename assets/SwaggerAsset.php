<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SwaggerAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        "/swagger/swagger-ui.css",
        "/swagger/main-swagger.css",
    ];
    public $js = [
        "/swagger/swagger-ui-bundle.js",
        "/swagger/swagger-ui-standalone-preset.js"
    ];
    public $depends = [
    ];
}