<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');

date_default_timezone_set('Europe/Moscow');
setlocale (LC_TIME, "RUS");

return [
    'id' => 'basic-console',
    'language' => 'ru_RU',
    'sourceLanguage' => 'ru-RU',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii', 'queue', 'reportQueue'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
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
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages'
                ],
            ],
        ],
        'log' => require(__DIR__ . '/log.php'),
        'db' => require(__DIR__ . '/db.php'),

        'cache' => $params['components']['cache'],
        'redis' => $params['components']['redis'],
        'queue' => $params['components']['queue'],
        'reportQueue' => $params['components']['reportQueue'],

        // Сервисы
        \app\services\PartnerService::class => \app\services\PartnerService::class,
        \app\services\PaySchetService::class => \app\services\PaySchetService::class,
        \app\services\CompensationService::class => \app\services\CompensationService::class,
        \app\services\RecurrentPaymentPartsService::class => \app\services\RecurrentPaymentPartsService::class,
        \app\services\PaymentService::class => \app\services\PaymentService::class,
        \app\services\ReportService::class => \app\services\ReportService::class,
        \app\services\LanguageService::class => \app\services\LanguageService::class,
        \app\services\YandexPayService::class => \app\services\YandexPayService::class,
        \app\services\PaymentTransferService::class => \app\services\PaymentTransferService::class,
        \app\services\PayToCardService::class => \app\services\PayToCardService::class,
        \app\services\CardRegisterService::class => \app\services\CardRegisterService::class,
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
];
