<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class CallbackController extends Controller
{

    public function actionMonetix()
    {
        $ips = Yii::$app->params['services']['payments']['Monetix']['callback_remote_ips'];
        if(!in_array(Yii::$app->request->remoteIP, $ips)) {
            throw new ForbiddenHttpException();
        }



    }

}