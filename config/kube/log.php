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
    '_SERVER.DATABASE_USER_PASSWORD',
    '_SERVER.DB_URL',
    '_SERVER.DB_URL_BASE'
];

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'app\services\logs\targets\ReqMaskJSONStdOutTarget',
            'levels' => ['warning'],
            'except' => [
                'yii\web\HttpException:401',
                'yii\web\HttpException:404',
            ],
            'maskVars' => $maskVars,
            'maxFileSize' => 1024 * 50,
            'maxLogFiles' => 20,
            'rotateByCopy' => false
        ],
        [
            'class' => 'app\services\logs\targets\SecurityJSONStdErrTarget',
            'levels' => ['error'],
            'except' => [
                'yii\web\HttpException:401',
                'yii\web\HttpException:404',
            ],
            'maskVars' => $maskVars,
            'maxFileSize' => 1024 * 50,
            'maxLogFiles' => 20,
            'rotateByCopy' => false
        ],
    ],
];