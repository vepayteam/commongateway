<?php
$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/test_db.php');

/**
 * @TODO Слить с основным конфигом (web.php)!
 */

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'language' => 'en-US',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'db' => $db,
        'mailer' => [
            'useFileTransport' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'user' => [
            'identityClass' => 'app\models\UserWgt',
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
            // but if you absolutely need it set cookie domain to localhost
            /*
            'csrfCookie' => [
                'domain' => 'localhost',
            ],
            */
        ],
        'cache' => $params['components']['cache'],
        'redis' => $params['components']['redis'],
        'queue' => $params['components']['queue'],

        // Сервисы
        \app\services\PartnerService::class => \app\services\PartnerService::class,
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
            'WalletoExchangeRateService' => ['class' => 'app\services\exchange_rates\WalletoExchangeRateService'],
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
