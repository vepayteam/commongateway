<?php
/**
 * This file is generated by the "yii asset" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version 2021-06-10 22:38:07
 */
return [
    'common' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'common-b37ac75505c58a47c819fdcb2b68b3ea.js',
        ],
        'css' => [
            'common-5a275f3505ac1d0e117cf42d7cdded9a.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'site' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'site-f30e2799f02b5c7e8c5a0d93f9d4e47e.js',
        ],
        'css' => [
            'site-4d02b7353b446b685627696e7bbc90a5.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'swagger' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'swagger-700b441e468978dde62c84f72d21313c.js',
        ],
        'css' => [
            'swagger-3db8efb3bd864ee5c0d02d2973b3ed1e.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'communal' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'communal-5e360f1b8124931cafd316c1ea0a8270.js',
        ],
        'css' => [
            'communal-2ebe8baffc0018df15ef64c7ec5e9041.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'partner' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'partner-9ed6ab3e80cd4836c741bfa9029d7835.js',
        ],
        'css' => [
            'partner-d30651ca33f36dda2a08fca71243d76f.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'pay' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'pay-07a857ef331d420799e7d9b0bc5399f7.js',
        ],
        'css' => [
            'pay-26d5739b2ec9724046c78940f51f56d8.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'widget' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'widget-71594548e353da3b23a00527f6603eb9.js',
        ],
        'css' => [
            'widget-ef6e03eae531339a61918e1b9e8fca8e.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'yii\\web\\JqueryAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'common',
        ],
    ],
    'yii\\web\\YiiAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'yii\\web\\JqueryAsset',
            'common',
        ],
    ],
    'yii\\bootstrap\\BootstrapAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'common',
        ],
    ],
    'yii\\bootstrap\\BootstrapPluginAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'yii\\web\\JqueryAsset',
            'yii\\bootstrap\\BootstrapAsset',
            'common',
        ],
    ],
    'yii\\widgets\\MaskedInputAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'yii\\web\\YiiAsset',
            'common',
        ],
    ],
    'yii\\widgets\\ActiveFormAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'yii\\web\\YiiAsset',
            'common',
        ],
    ],
    'app\\assets\\CommonAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'yii\\web\\YiiAsset',
            'yii\\web\\JqueryAsset',
            'yii\\bootstrap\\BootstrapAsset',
            'yii\\bootstrap\\BootstrapPluginAsset',
            'yii\\widgets\\MaskedInputAsset',
            'yii\\widgets\\ActiveFormAsset',
            'common',
        ],
    ],
    'app\\assets\\InsAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'app\\assets\\CommonAsset',
            'site',
        ],
    ],
    'app\\assets\\SiteAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'app\\assets\\InsAsset',
            'site',
        ],
    ],
    'app\\assets\\SwaggerAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'swagger',
        ],
    ],
    'app\\assets\\MerchantAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'app\\assets\\CommonAsset',
            'communal',
        ],
    ],
    'app\\assets\\PartnerAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'app\\assets\\CommonAsset',
            'partner',
        ],
    ],
    'app\\assets\\PayAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'app\\assets\\CommonAsset',
            'pay',
        ],
    ],
    'app\\assets\\WidgetAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'app\\assets\\CommonAsset',
            'widget',
        ],
    ],
];
