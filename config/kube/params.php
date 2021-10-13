<?php

return [
    'domain' => getenv('TEST_URL', true),
    'adminEmail' => 'support@vepay.online',
    'robotEmail' => 'robot@vepay.online',
    'infoEmail' => 'support@vepay.online',
    'buhEmail' => 'support@vepay.online',
    'dectaApiUrl' => 'https://gate.decta.com',
    'DEVMODE' => boolval(getenv('DEVMODE', true)) ? 'Y' : 'N',
    'TESTMODE' => 'Y',
    'accountServiceUrl' => '',
	'key' => '4l80z8E9s0',
    'keycancel' => 'Q0YimN4R5rPL3uld8094Rz85E4E5h93sR0',
    'info' => [
        'email' => 'support@vepay.online',
        'phone' => '+7 (499) 954-84-95',
        'address' => 'Москва, Нижний Сусальный переулок, 5с18',
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
            'hostname' => getenv('REDIS_HOST', true),
            'port' => getenv('REDIS_PORT', true),
            'database' => getenv('REDIS_DB_NUM_QUEUE', true),
            'retries' => 1,
        ],
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'queue',
            'attempts' => 10,
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
            'url' => 'https://vpbc-102-test.vepay.cf/api',
            'superuserLogin' => 'superuser',
            'superuserPassword' => 'Default12345',
            'canRegUserRole' => 'php_account_admin',
        ],
        'payments' => [
            'BRS' => [
                'url' => getenv('PARAMS_PAYMENTS_BRS_URL', true),
                'url_3ds' => getenv('PARAMS_PAYMENTS_BRS_URL_3DS', true),
                'url_xml' => getenv('PARAMS_PAYMENTS_BRS_URL_XML', true),
                'url_b2c' => getenv('PARAMS_PAYMENTS_BRS_URL_B2C', true),
            ],
        ],
    ],
    'login_user_token_valid_time' => 60*60,
    'support_email' => 'support@vepay.online',
        'remote_ip' => getenv('REMOTE_IP', true)
];
