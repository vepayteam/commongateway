<?php
/**
 * This file is generated by the "yii asset" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version 2021-07-08 15:54:23
 */
return [
    'common' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'common-3f896ecb9a754fd01e01d9a5501d734a.js',
        ],
        'css' => [
            'common-073354e39f172fa39d84f6234130f74e.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'site' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'site-517548ad14d64ca7d3df810321dcd967.js',
        ],
        'css' => [
            'site-f14e94c03f75d780f5fa4e668447f0c9.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'swagger' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'swagger-2174fcbd2f61ee8b70a8375b2b7b77b9.js',
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
            'communal-6c1582fa38f17b79c1d4855bbcf9d413.js',
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
            'partner-2519ec59c3228cdc043c5b2a2dec40b3.js',
        ],
        'css' => [
            'partner-80a12d5c5dd4e21c66b577e5a3c511a6.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'pay' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'pay-e3acd4c7dfef9d27e7669349f36711ce.js',
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
            'widget-e6faa059a4e92f02eb2dd909ff1e8076.js',
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