<?php
/**
 * This file is generated by the "yii asset" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version 2022-04-21 19:43:47
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
            'common-24216a87f3f7031aa20f8a90f4c6f665.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'site' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'site-9a48773aceb4940abaf750e1a027a561.js',
        ],
        'css' => [
            'site-fd30b5c12b1da2b1bad0cdeb43dced93.css',
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
            'partner-272d53111cdbdee07722da645cb1aaea.js',
        ],
        'css' => [
            'partner-3416918c3a07f2274887a49205f1043f.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'pay' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'pay-d60a80463d308b8c0eac83bd3852a0bb.js',
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