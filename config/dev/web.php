<?php
$params = require(__DIR__ . '/params.php');

date_default_timezone_set('Europe/Moscow');
setlocale(LC_TIME, "RUS");
ini_set('max_execution_time', 120);
ini_set('memory_limit', '512M');
ini_set('session.gc_maxlifetime', 3600 * 24);
ini_set('session.cookie_lifetime', 0);

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'ru_RU',
    'timezone' => 'Europe/Moscow',
    'defaultRoute' => 'site',
    'bootstrap' => ['log', 'queue'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@runtime' => '@app/runtime',
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
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
        ],
        'response' => [
            'on beforeSend' => function ($event) {
                $request = Yii::$app->request;
                $routesXFrameOptionsNone = [
                    '/widget/',
                    '/pay/'
                ];

                $sendXFrame = true;
                foreach ($routesXFrameOptionsNone as $url) {
                    if (str_starts_with($request->url, $url)) {
                        $sendXFrame = false;
                        break;
                    }
                }

                if ($sendXFrame) {
                    $event->sender->headers->add('X-Frame-Options', 'SAMEORIGIN');
                }
            },
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
                '/mfo/getsbpbankreceiver' => 'mfo/default/getsbpbankreceiver',

                'POST /h2hapi/v1/invoices' => '/h2hapi/v1/invoice/post',
                'GET /h2hapi/v1/invoices/<id:\d+>' => '/h2hapi/v1/invoice/get',
                'PUT /h2hapi/v1/invoices/<paySchetId:\d+>/payment' => '/h2hapi/v1/payment/put',

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
        'log' => require(__DIR__ . '/log.php'),
        'db' => require(__DIR__ . '/db.php'),

        'cache' => $params['components']['cache'],
        'redis' => $params['components']['redis'],
        'queue' => $params['components']['queue'],

        // Сервисы
        \app\services\PartnerService::class => \app\services\PartnerService::class,
        \app\services\PaySchetService::class => \app\services\PaySchetService::class,
        \app\services\CompensationService::class => \app\services\CompensationService::class,
        \app\services\RecurrentPaymentPartsService::class => \app\services\RecurrentPaymentPartsService::class,
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
            'WallettoExchangeRateService' => ['class' => 'app\services\exchange_rates\WallettoExchangeRateService'],
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
        'h2hapi' => [
            'class' => 'app\modules\h2hapi\Module',
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
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'job' => [
                'class' => \yii\queue\gii\Generator::class,
            ],
        ],
        'allowedIPs' => ['*'],
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

return $config;
