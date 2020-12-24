<?php

return [
    'domain' => 'http://dev.vepay.online',
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

    'services' => [
        'accounts' => [
            'url' => 'http://vpbc-102-test.192-168-110-2.nip.io/api',
            'superuserLogin' => 'superuser',
            'superuserPassword' => 'Default12345',
            'canRegUserRole' => 'php_account_admin',
        ],
    ],
	
];
