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
    'traceLevel' => 0,
    'targets' => [
        [
            'class' => 'app\services\logs\targets\SecurityFileTarget',
            'levels' => ['warning', 'info'],
            'maskVars' => $maskVars,
            'maxFileSize' => 1024 * 50,
            'maxLogFiles' => 50,
            'rotateByCopy' => false,
            'microtime' => true,
            'except' => [
                'yii\web\HttpException:401',
                'yii\web\HttpException:404',
            ]
        ],
        [
            'class' => 'app\services\logs\targets\SecurityFileTarget',
            'levels' => ['error'],
            'maskVars' => $maskVars,
            'maxFileSize' => 1024 * 50,
            'maxLogFiles' => 50,
            'rotateByCopy' => false,
            'microtime' => true,
            'except' => [
                'yii\web\HttpException:401',
                'yii\web\HttpException:404',
            ]
        ],
    ],
];