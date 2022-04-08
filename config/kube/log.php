<?php

$maskVars = [
    '_POST.login',
    '_POST.passw',
    '_POST.token',
    '_POST.cardnum',
    '_POST.Provparams',
    '_POST.PayForm.CardNumber',
    '_POST.PayForm.CardCVC',
    '_POST.InsertKey',
    '_POST.ChangeKeys',
    '_SESSION.__id'
];

$logVars = [
    '_GET',
    '_POST',
    '_FILES',
    '_COOKIE',
    '_SESSION',
    '_SERVER.HTTP_USER_AGENT',
    '_SERVER.HTTP_ACCEPT',
    '_SERVER.HTTP_REFERER',
    '_SERVER.CONTENT_TYPE',
    '_SERVER.SERVER_ADDR',
    '_SERVER.SERVER_PORT',
    '_SERVER.REMOTE_ADDR',
    '_SERVER.SCRIPT_FILENAME',
    '_SERVER.REDIRECT_URL',
    '_SERVER.SERVER_PROTOCOL',
    '_SERVER.REQUEST_METHOD',
    '_SERVER.QUERY_STRING',
    '_SERVER.REQUEST_URI',
    '_SERVER.REQUEST_TIME',
    '_SERVER.REDIRECT_QUERY_STRING'
];

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['info', 'error', 'warning'],
            'maskVars' => [
                '_SERVER.HTTP_AUTHORIZATION',
                '_SERVER.PHP_AUTH_USER',
                '_SERVER.PHP_AUTH_PW',
                '_POST.cardnum',
                '_POST.Provparams',
                '_POST.PayForm.CardNumber',
                '_POST.PayForm.CardCVC',
                '_POST.InsertKey',
                '_POST.ChangeKeys'
            ],
            'maxFileSize' => 1024 * 50,
            'maxLogFiles' => 20,
            'rotateByCopy' => false
        ],
    ],
];