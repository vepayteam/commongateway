<?php
$params = require(__DIR__ . '/params.php');

date_default_timezone_set('Europe/Moscow');
setlocale (LC_TIME, "RUS");
ini_set('max_execution_time', 120);
ini_set('memory_limit','512M');
ini_set('session.gc_maxlifetime',3600 * 24);
ini_set('session.cookie_lifetime',0);

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'ru_RU',
    'defaultRoute' => 'site',
    'bootstrap' => ['log', 'queue'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@runtime'=>'@app/runtime',
        '@web' => $params['domain'],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'm04W7J9wtXYYl5Hp4f51QbvERNbMgJgPq',
            'baseUrl' => '',
            //'parsers' => [
                //'application/json' => 'yii\web\JsonParser',
            //],
        ],
        'session' => [
            'class' => 'yii\web\DbSession',
        ],
        'assetManager' => [
            'appendTimestamp' => YII_DEBUG ? true : false,
            'bundles' => [
                /*'yii\web\JqueryAsset' => [
                    'sourcePath' => null,
                    'basePath' => '@webroot',
                    'baseUrl' => '@web',
                    'js' => [
                        '/aassets/plugins/jquery-2.2.4.min.js',
                    ]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'sourcePath' => null,
                    'basePath' => '@webroot',
                    'baseUrl' => '@web',
                    'css' => [
                        '/aassets/plugins/bootstrap/css/bootstrap.min.css',
                    ]
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'sourcePath' => null,
                    'basePath' => '@webroot',
                    'baseUrl' => '@web',
                    'js' => [
                        '/aassets/plugins/bootstrap/js/bootstrap.min.js',
                    ]
                ],*/
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            //'enableStrictParsing' => true,
            'rules' => [
                'partner/index' => 'partner/default/index',
                'partner/login' => 'partner/default/login',
                'partner/logout' => 'partner/default/logout',

                '/oferta' => '/site/oferta',
                '/admin/statementdiff/<id:\d+>' => 'admin/statement-diff',
                '/admin/syncbalance/<id:\d+>' => 'admin/syncbalance',

                '<controller>/<id:\d+>' => '<controller>/index',
                '<controller>/<action>' => '<controller>/<action>',
                '<controller>/<action>/<id:\d+>' => '<controller>/<action>',
                '<module>/<controller>/<action>' => '<module>/<controller>/<action>',
                '<module>/<controller>/<action>/<id:\d+>' => '<module>/<controller>/<action>',
                '<module>/<action>' => '<module>/default/<action>',
                '<module>/<action>/<id:\d+>' => '<module>/default/<action>',
                'debug/<controller>/<action>' => 'debug/<controller>/<action>',
                '<version:\w+>/<module>/<controller>/<action>' => '<module>/<controller>/<action>',

                //['class' => 'yii\rest\UrlRule', 'controller' => ''],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\partner\UserLk',
            'loginUrl' => ['partner'],
            'enableAutoLogin' => false
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'viewPath' => '@app/mail/', // Путь до папки с шаблоном
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'exchange.dengisrazy.ru',//'localhost',
                'username' => 'dengisrazy.ru\robot-vepay',
                'password' => 'Sheel6ah',
                'port' => '587',
                'encryption' => 'tls',
                'streamoptions' => ['ssl' => ['verify_peer' => FALSE, 'verify_peer_name' => FALSE]]
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'maskVars' => [
                        '_SERVER.HTTP_AUTHORIZATION',
                        '_SERVER.PHP_AUTH_USER',
                        '_SERVER.PHP_AUTH_PW',
                        '_POST.cardnum',
                        '_POST.Provparams',
                        '_POST.PayForm.CardNumber',
                        '_POST.PayForm.CardCVC',
                        '_POST.InsertKey',
                        '_POST.ChangeKeys'
                    ],
                    'maxFileSize' => 1024 * 50,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                    'except' => [
                        'yii\db\Exception',
                    ]
                ],
                [
                    'class' => 'app\services\logs\targets\DbFileTarget',
                    'levels' => ['error', 'warning'],
                    'maskVars' => [
                        '_SERVER.HTTP_AUTHORIZATION',
                        '_SERVER.PHP_AUTH_USER',
                        '_SERVER.PHP_AUTH_PW',
                        '_POST.cardnum',
                        '_POST.Provparams',
                        '_POST.PayForm.CardNumber',
                        '_POST.PayForm.CardCVC',
                        '_POST.InsertKey',
                        '_POST.ChangeKeys'
                    ],
                    'maxFileSize' => 1024 * 50,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                    'categories' => [
                        'yii\db\Exception',
                    ]
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),

        'redis' => $params['components']['redis'],
        'queue' => $params['components']['queue'],
    ],
    'params' => $params,
    'container' => [
        'singletons' => [
            'PaymentService' => ['class' => 'app\services\payment\PaymentService'],
            'BalanceService' => ['class' => 'app\services\balance\BalanceService'],
            'IdentService' => ['class' => 'app\services\ident\IdentService'],
            'PartnersService' => ['class' => 'app\services\partners\PartnersService'],
            'AuthService' => ['class' => 'app\services\auth\AuthService'],
            'NotificationsService' => ['class' => 'app\services\notifications\NotificationsService'],
        ],
    ],
    'modules' => [
        'partner' => [
            'class' => 'app\modules\partner\Module',
        ],
        'mfo' => [
            'class' => 'app\modules\mfo\Module',
        ],
        'kfapi' => [
            'class' => 'app\modules\kfapi\Module',
        ],
        'keymodule' => [
            'class' => 'app\modules\keymodule\Module',
        ],
        'antifraud' => [
            'class' => 'app\modules\antifraud\Module',
        ],
        'lk' => [
            'class' => 'app\modules\lk\Module',
        ],
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'panels' => [
            'queue' => \yii\queue\debug\Panel::class,
        ],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'job' => [
                'class' => \yii\queue\gii\Generator::class,
            ],
        ],
    ];

    Yii::setAlias('@img', '');
} else {
    Yii::setAlias('@img', '');
    $config['components']['assetManager'] = ['bundles' => require 'assets-prod.php'];
    if (!($params['DEVMODE'] == 'Y' || $params['TESTMODE'] == 'Y')) {
        $config['as hostControl'] = [
            'class' => 'yii\filters\HostControl',
            'allowedHosts' => [
                'api.vepay.online',
                'test.vepay.online',
                'dev.vepay.online',
                'api.vepay.local'
            ],
            'fallbackHostInfo' => 'https://api.vepay.online',
        ];
    }
}

$config['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['russrandart'],
    'logFile' => '@app/runtime/logs/console/russrandart.log',
    'maxFileSize' => 1024 * 2,
    'maxLogFiles' => 20,
    'logVars' => [],
];

$config['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['rsbcron', 'queue'],
    'logFile' => '@app/runtime/logs/console/rsbcron.log',
    'maxFileSize' => 1024 * 30,
    'maxLogFiles' => 20,
    'rotateByCopy' => false,
    'logVars' => [],
];
$config['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['mfo'],
    'logFile' => '@app/runtime/logs/mfo.log',
    'maxFileSize' => 1024 * 10,
    'maxLogFiles' => 20,
    'rotateByCopy' => false,
    'logVars' => [],
];

$config['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['merchant'],
    'logFile' => '@app/runtime/logs/console/merchant.log',
    'maxFileSize' => 1024 * 30,
    'maxLogFiles' => 20,
    'rotateByCopy' => false,
    'logVars' => [],
];

return $config;
