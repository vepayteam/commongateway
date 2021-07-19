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
            'class' => 'app\services\logs\targets\ReqMaskJSONStdOutTarget',
            'levels' => array_filter(['warning', 'info', YII_DEBUG ? 'trace' : '']),
            'except' => [
                'yii\web\HttpException:401',
                'yii\web\HttpException:404',
            ],
            'maskVars' => $maskVars,
            'logVars' => $logVars
        ],
        [
            'class' => 'app\services\logs\targets\SecurityJSONStdErrTarget',
            'levels' => ['error'],
            'except' => [
                'yii\web\HttpException:401',
                'yii\web\HttpException:404',
            ],
            'maskVars' => $maskVars,
            'logVars' => $logVars
        ],
    ],
];