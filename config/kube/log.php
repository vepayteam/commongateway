<?php

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'app\services\logs\targets\ReqMaskStdOutTarget',
            'levels' => ['warning'],
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
            ]
        ],
        [
            'class' => 'app\services\logs\targets\SecurityStdErrTarget',
            'levels' => ['error'],
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
            ]
        ],
    ],
];