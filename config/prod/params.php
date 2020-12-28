<?php

return [
    'domain' => 'https://api.vepay.online',
    'adminEmail' => 'support@vepay.online',
    'robotEmail' => 'robot@vepay.online',
    'infoEmail' => 'support@vepay.online',
    'buhEmail' => 'support@vepay.online',
    'DEVMODE' => 'N',
    'TESTMODE' => 'N',
    'accountServiceUrl' => '',
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

    'services' => [
        'accounts' => [
            'url' => '',
            'superuserLogin' => 'superuser',
            'superuserPassword' => '',
            'canRegUserRole' => 'php_account_admin',
        ],
        'ident' => [
            'runaDomain' => 'https://ecommerce.runabank.ru/pc4x4',
            'runaLogin' => '784',
            'runaMode' => 'verify_docs'
        ],
    ],
    'login_user_token_valid_time' => 60*60,
	
];
