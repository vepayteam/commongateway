<?php
/**
 * This file is generated by the "yii asset" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version 2022-08-10 09:47:53
 */
return [
    'common' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'common-573eda2ac477ff1a0bf11c9805dae523.js',
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
            'site-74ce5ca03321fa4bf49486835f82ba7f.js',
        ],
        'css' => [
            'site-d697e2c2d08e8adbcfcce903d6e8c4c5.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'swagger' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'swagger-d14be958e23ede0f26168be1711bd09a.js',
        ],
        'css' => [
            'swagger-3e91ccac8173fbe79464ddc57617e52c.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'communal' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'communal-ffc339115d426c6830d6b96f675c2e2c.js',
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
            'partner-34edea32c0eea4a11d63215044771141.js',
        ],
        'css' => [
            'partner-61c66add7e2b7d29edb460a54050f71d.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'pay' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'pay-69cc67d2ce9da6ed64e3a3960b7019e7.js',
        ],
        'css' => [
            'pay-8a0a6582b7cc14832fdf846f48eeeace.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'widget' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'widget-c87f225275ad5e014eccbe88fdf73d6e.js',
        ],
        'css' => [
            'widget-ef6e03eae531339a61918e1b9e8fca8e.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'p2p' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'p2p-02518981271aca52142fb8c7bfe488e2.js',
        ],
        'css' => [
            'p2p-d91fd73b236226bfdafc6f43bbb074b4.css',
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
    'app\\assets\\P2pAsset' => [
        'sourcePath' => null,
        'js' => [],
        'css' => [],
        'depends' => [
            'p2p',
        ],
    ],
];