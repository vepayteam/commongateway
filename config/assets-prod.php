<?php
/**
 * This file is generated by the "yii asset" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version 2020-09-25 08:23:23
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
            'common-9ea4ceba402ce3c925f222f778f76b79.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'site' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'site-6d16afb6fe859cc39a5c65a324feb2dd.js',
        ],
        'css' => [
            'site-4f7815602f5ad1bc03960753f7152cb6.css',
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
            'communal-172d341d245ee343b9bf0f9ec90c3df5.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'partner' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'partner-108e2be90fa7fbde81ad389b21be62f0.js',
        ],
        'css' => [
            'partner-618d0146a7e91957c4673a28e1a80dfb.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'pay' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'pay-441ed4cabf7b3d6de07db8994283fd65.js',
        ],
        'css' => [
            'pay-70b79ef0add4f3ea0f74f927f1d740b7.css',
        ],
        'depends' => [],
        'sourcePath' => null,
    ],
    'widget' => [
        'class' => 'yii\\web\\AssetBundle',
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'js' => [
            'widget-6b7351418f02daf97106bc527bdaaf4d.js',
        ],
        'css' => [
            'widget-fc04b7c1d66d8e69b1ef023994eddd5f.css',
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