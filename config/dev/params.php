<?php

return [
    'domain' => 'http://localhost:8000',
    'adminEmail' => 'support@vepay.online',
    'robotEmail' => 'robot@vepay.online',
    'infoEmail' => 'support@vepay.online',
    'buhEmail' => 'support@vepay.online',
    'dectaApiUrl' => 'https://gate.decta.com',
    'dectaProxy' => 'http://username:password@vepay-proxy.virtualfort.ru:30013',
    'DEVMODE' => 'Y',
    'TESTMODE' => 'Y',
    'key' => '',
    'keycancel' => '',
    'info' => [
        'email' => '',
        'phone' => '',
        'address' => '',
    ],
    'tcb' => [
        'id' => '',
        'key' => '',
    ],
    'tcbMfo' => [
        'id' => '',
        'key' => '',
    ],
    'tcbCard' => [
        'id' => '',
        'key' => '',
    ],
    'tcbEcom' => [
        'id' => '',
        'key' => '',
    ],

    'kkt' => [
        'urlico' => '',
        'inn' => "",
        'sno' => "",
        'host' => '',
        'token' => ''
    ],

    'testCards' => require(__DIR__ . '/test_cards.php'),
    'testParams' => require(__DIR__ . '/test_params.php'),

    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 3,
            'retries' => 3
        ],
        'queue' => [
            'class' => \app\services\queue\RedisQueueTraceId::class,
            'redis' => 'redis',
            'channel' => 'queue',
            'attempts' => 10,
        ],
        'reportQueue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'report_queue',
            'on afterError' => function (\yii\queue\ExecEvent $event) {
                if ($event->error) {
                    Yii::$app->errorHandler->logException($event->error);
                }
            }
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 4
            ],
        ],
    ],

    'services' => [
        'accounts' => [
            'url' => 'http://vpbc-102-test.192-168-110-2.nip.io/api',
            'superuserLogin' => 'superuser',
            'superuserPassword' => 'Default12345',
            'canRegUserRole' => 'php_account_admin',
        ],
        'payments' => [
            'BRS' => [
                'url' => 'https://testsecurepay2.rsb.ru:9443',
                'url_3ds' => 'https://testsecurepay2.rsb.ru/ecomm2/ClientHandler',
                'url_p2p' => 'https://testsecurepay.rsb.ru:9443',
                'url_p2p_3ds' => 'https://testsecurepay.rsb.ru/ecomm2/ClientHandler',
                'url_xml' => 'https://194.67.29.216:8443',
                'url_b2c' => 'https://212.46.217.150:7601',
            ],
            'TCB' => [
                'url' => 'https://paytest.online.tkbbank.ru',
                'url_xml' => 'https://193.232.101.14:8203',
            ],
        ],
    ],
    'login_user_token_valid_time' => 60 * 60,
    'support_email' => 'support@vepay.online',
    'remote_ip' => '',
];
