<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class P2pAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'payasset/css/p2pform.css',
    ];
    public $js = [
        'payasset/js/ua-parser.js', //парсер строк юзерагента
        'payasset/js/jquery.inputmask.min.js',
        'payasset/js/p2pform.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
