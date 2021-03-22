<?php

$maskVars = [
    '_SERVER.HTTP_AUTHORIZATION',
    '_SERVER.PHP_AUTH_USER',
    '_SERVER.PHP_AUTH_PW',
    '_POST.login',
    '_POST.passw',
    '_POST.token',
    '_POST.cardnum',
    '_POST.Provparams',
    '_POST.PayForm.CardNumber',
    '_POST.PayForm.CardCVC',
    '_POST.InsertKey',
    '_POST.ChangeKeys',
    '_SESSION.__id',
    '_SERVER.REDIRECT_REDIRECT_DATABASE_USER',
    '_SERVER.REDIRECT_REDIRECT_DATABASE_USER_PASSWORD',
    '_SERVER.REDIRECT_DATABASE_USER',
    '_SERVER.REDIRECT_DATABASE_USER_PASSWORD',
    '_SERVER.DATABASE_USER',
    '_SERVER.DATABASE_USER_PASSWORD'
];

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'app\services\logs\targets\ReqMaskStdOutTarget',
            'levels' => ['warning'],
            'maskVars' => $maskVars
        ],
        [
            'class' => 'app\services\logs\targets\SecurityStdErrTarget',
            'levels' => ['error'],
            'maskVars' => $maskVars
        ],
    ],
];