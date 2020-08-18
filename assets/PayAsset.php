<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PayAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //font
        //'https://fonts.googleapis.com/css?family=M+PLUS+1p:400,500,700&display=swap&subset=cyrillic,cyrillic-ext',
        'payasset/mplus1p/mplus1p.css',
        'aassets/fonts/font-awesome-master/css/font-awesome.min.css',
        'insasset/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
        'payasset/css/style.css',
        'payasset/css/payform.css',
    ];
    public $js = [
        'payasset/js/ua-parser.js', //парсер строк юзерагента
        'payasset/js/fingerprint2.js', //отпечаток пользователя
        'payasset/js/tracking.js', //компоновка работы ua-parser + fingerprint
        'payasset/js/payform.js',
        'payasset/js/formdata.js',
        'payasset/js/customvalidation.js',
    ];
    public $depends = [
        'app\assets\CommonAsset'
    ];
}
