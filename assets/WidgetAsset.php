<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class WidgetAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //font
        'payasset/ttnorms/stylesheet.css',
        'aassets/fonts/font-awesome-master/css/font-awesome.min.css',
        'insasset/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
        'payasset/css/style.css',
        'payasset/css/widget.css'
    ];
    public $js = [
        'payasset/js/ua-parser.js', //парсер строк юзерагента
        'payasset/js/fingerprint2.js', //отпечаток пользователя
        'payasset/js/tracking.js', //компоновка работы ua-parser + fingerprint
        'payasset/js/widgetform.js',
        'payasset/js/customvalidation.js',
    ];
    public $depends = [
        'app\assets\CommonAsset'
    ];
}