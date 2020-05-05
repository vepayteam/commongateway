<?php
/**
 * Configuration file for the "yii asset" console command.
 *
 * npm install uglify-es -g
 * npm i clean-css-cli -g
 */

// In the console environment, some path aliases may not exist. Please define these:
Yii::setAlias('@webroot', __DIR__ . '/web');
Yii::setAlias('@web', '/');
Yii::setAlias('@bower', __DIR__ . '/vendor/bower-asset');
Yii::setAlias('@npm', __DIR__ . '/vendor/npm-asset'); 

return [
    // Adjust command/callback for JavaScript files compressing:
    //'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    'jsCompressor' => 'uglifyjs {from} -m  -o {to}',
    // Adjust command/callback for CSS files compressing:
    //'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    'cssCompressor' => 'cleancss {from} --output {to}',	
    // Whether to delete asset source after compression:
    'deleteSource' => false,
    // The list of asset bundles to compress:
    'bundles' => [
        'app\assets\CommonAsset',
        'app\assets\InsAsset',
        'app\assets\SiteAsset',
        'app\assets\SwaggerAsset',
        'app\assets\MerchantAsset',
        'app\assets\PartnerAsset',
        'app\assets\PayAsset',
        'app\assets\WidgetAsset',
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\widgets\MaskedInputAsset',
        'yii\widgets\ActiveFormAsset',
    ],
    // Asset bundle for compression output:
    'targets' => [
        'common' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'common-{hash}.js',
            'css' => 'common-{hash}.css',
            'depends' => [
                // Include all assets shared between 'backend' and 'frontend'
                'yii\web\YiiAsset',
                'yii\web\JqueryAsset',
                'yii\bootstrap\BootstrapAsset',
                'yii\bootstrap\BootstrapPluginAsset',
                'yii\widgets\MaskedInputAsset',
                'yii\widgets\ActiveFormAsset',
				'app\assets\CommonAsset'
            ],
        ],
        'site' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'site-{hash}.js',
            'css' => 'site-{hash}.css',
            'depends' => [
                // Include only 'backend' assets:
                'app\assets\InsAsset',
                'app\assets\SiteAsset'
            ],
        ],
        'swagger' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'swagger-{hash}.js',
            'css' => 'swagger-{hash}.css',
            'depends' => [
                // Include only 'backend' assets:
				'app\assets\SwaggerAsset'
            ],            
        ],
        'communal' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'communal-{hash}.js',
            'css' => 'communal-{hash}.css',
            'depends' => [
                // Include only 'backend' assets:
                'app\assets\MerchantAsset'
            ],            
        ],
        'partner' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'partner-{hash}.js',
            'css' => 'partner-{hash}.css',
            'depends' => [
                // Include only 'backend' assets:
                'app\assets\PartnerAsset',
            ],            
        ],
        'pay' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'pay-{hash}.js',
            'css' => 'pay-{hash}.css',
            'depends' => [
                'app\assets\PayAsset'
            ],
        ],
        'widget' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'widget-{hash}.js',
            'css' => 'widget-{hash}.css',
            'depends' => [
                'app\assets\WidgetAsset'
            ],
        ]
    ],
    // Asset manager configuration:
    'assetManager' => [
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
        'bundles' => [
        ]
    ],
];