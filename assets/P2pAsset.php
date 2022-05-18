<?php

namespace app\assets;

use yii\web\AssetBundle;

class P2pAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'payasset/css/reset.css',
        'payasset/css/roboto-fonts.css',
        'payasset/css/p2pform.css',
        'payasset/css/tooltipster.main.min.css',
        'payasset/css/tooltipster.bundle.min.css',
    ];

    public $js = [
        'payasset/js/jquery-1.12.4.min.js',
        'payasset/js/ua-parser.js', //парсер строк юзерагента
        'payasset/js/jquery.inputmask.min.js',
        'payasset/js/tooltipster.main.min.js',
        'payasset/js/tooltipster.bundle.min.js',
        'payasset/js/p2pform.js',
    ];
}
