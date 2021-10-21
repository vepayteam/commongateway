<?php

return [
    'domain' => 'http://localhost:8000',
    'adminEmail' => 'support@vepay.online',
    'robotEmail' => 'robot@vepay.online',
    'infoEmail' => 'support@vepay.online',
    'buhEmail' => 'support@vepay.online',
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
            'class' => 'yii\base\BaseObject',
        ],
        'queue' => [
            'class' => \app\services\queue\RedisQueueTraceId::class,
            'db' => 'db', // DB connection component or its config
            'tableName' => '{{%queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
            'as log' => \yii\queue\LogBehavior::class,
            'attempts' => 3, // Максимальное кол-во попыток
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => getenv('REDIS_HOST', true),
                'port' => getenv('REDIS_PORT', true),
                'database' => getenv('REDIS_DB_NUM_CACHE', true),
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
                'url_xml' => 'https://194.67.29.216:8443',
                'url_b2c' => 'https://212.46.217.150:7601',
            ],
        ],
    ],
    'login_user_token_valid_time' => 60*60,
    'support_email' => 'support@vepay.online',
    'remote_ip' => '',
];
