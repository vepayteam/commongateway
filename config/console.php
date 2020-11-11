<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
//$db = require(__DIR__ . '/db.php');

date_default_timezone_set('Europe/Moscow');
setlocale (LC_TIME, "RUS");

$console = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii', 'queue'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'maxFileSize' => 1024 * 50,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false
                ],
            ],
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
        'db' => require(__DIR__ . '/db.php'),
        'queue' => [
            'class' => \yii\queue\db\Queue::class,
            'db' => 'db', // DB connection component or its config
            'tableName' => '{{%queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
            'as log' => \yii\queue\LogBehavior::class,
        ],
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
];

$console['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['russrandart'],
    'logFile' => '@app/runtime/logs/console/russrandart.log',
    'maxFileSize' => 1024 * 2,
    'maxLogFiles' => 20,
    'logVars' => [],
];

$console['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['rsbcron'],
    'logFile' => '@app/runtime/logs/console/rsbcron.log',
    'maxFileSize' => 1024 * 30,
    'maxLogFiles' => 20,
    'rotateByCopy' => false,
    'logVars' => [],
];
$console['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['merchant'],
    'logFile' => '@app/runtime/logs/console/merchant.log',
    'maxFileSize' => 1024 * 30,
    'maxLogFiles' => 20,
    'rotateByCopy' => false,
];
$console['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['reestr'],
    'logFile' => '@app/runtime/logs/console/reestr.log',
    'maxFileSize' => 1024 * 10,
    'maxLogFiles' => 20,
    'logVars' => [],
];
$console['components']['log']['targets'][] = [
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning', 'info'],
    'categories' => ['payreestr'],
    'logFile' => '@app/runtime/logs/console/payreestr.log',
    'maxFileSize' => 1024 * 10,
    'maxLogFiles' => 20,
    'logVars' => [],
];

return $console;
