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
    '_SESSION.__id'
];

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'app\services\logs\targets\ReqMaskFileTarget',
            'levels' => ['warning'],
            'maskVars' => $maskVars,
            'maxFileSize' => 1024 * 50,
            'maxLogFiles' => 20,
            'rotateByCopy' => false,
        ],
        [
            'class' => 'app\services\logs\targets\SecurityFileTarget',
            'levels' => ['error'],
            'maskVars' => $maskVars,
            'maxFileSize' => 1024 * 50,
            'maxLogFiles' => 20,
            'rotateByCopy' => false,
        ],
    ],
];