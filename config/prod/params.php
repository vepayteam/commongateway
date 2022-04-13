<?php

return [
    'domain' => 'https://api.vepay.online',
    'adminEmail' => 'support@vepay.online',
    'robotEmail' => 'robot@vepay.online',
    'infoEmail' => 'support@vepay.online',
    'buhEmail' => 'support@vepay.online',
    'VERBOSE' => 'N',
    'DEVMODE' => 'N',
    'TESTMODE' => 'N',
    'accountServiceUrl' => '',
    'dectaApiUrl' => 'https://gate.decta.com',
    'dectaProxy' => 'http://username:password@vepay-proxy.virtualfort.ru:30013',
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
    'tcbConnectionTimeout' => null,

    'kkt' => [
        'urlico' => '',
        'inn' => "",
        'sno' => "",
        'host' => '',
        'token' => ''
    ],

    'testCards' => require(__DIR__ . '/test_cards.php'),
    'testParams' => [],

    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 3,
            'retries' => 1,
        ],
        'queue' => [
            'class' => \app\services\queue\RedisQueueTraceId::class,
            'redis' => 'redis',
            'channel' => 'queue',
            'attempts' => 10,
            'on afterError' => function (\yii\queue\ExecEvent $event) {
                if ($event->error) {
                    Yii::$app->errorHandler->logException($event->error);
                }
            }
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
                'database' => 4,
            ],
        ],
    ],

    'services' => [
        'accounts' => [
            'url' => '',
            'superuserLogin' => 'superuser',
            'superuserPassword' => '',
            'canRegUserRole' => 'php_account_admin',
        ],
        'payments' => [
            'BRS' => [
                'url' => 'https://securepay.rsb.ru:9443',
                'url_3ds' => 'https://securepay.rsb.ru/ecomm2/ClientHandler',
                'url_p2p' => 'https://securepay.rsb.ru:9443',
                'url_p2p_3ds' => 'https://securepay.rsb.ru/ecomm2/ClientHandler',
                'url_xml' => 'https://194.67.29.215:8443',
                'url_b2c' => 'https://212.46.217.150:7603',
            ],
            'TCB' => [
                'url' => 'https://pay.tkbbank.ru',
                'url_xml' => 'https://pay.tkbbank.ru:8204',
            ],
        ],
    ],
    'login_user_token_valid_time' => 60*60,
    'support_email' => 'support@vepay.online',
    'remote_ip' => '',
];
