<?php

namespace app\models\api;

trait CorsTrait
{
    public function updateBehaviorsCors(&$behaviors)
    {
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];
    }

    public function checkBeforeAction()
    {
        if (\Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            /*
              Access-Control-Allow-Headers: Authorization
              Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS
              Access-Control-Allow-Origin: https://resttesttest.com
              Access-Control-Expose-Headers:
              Access-Control-Max-Age: 86400
             */

            $cors = [
                'Access-Control-Allow-Origin' => '',
                'Access-Control-Allow-Headers' => '',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS',
                'Access-Control-Expose-Headers' => '',
                'Access-Control-Max-Age' => 86400
            ];
            if (isset(\Yii::$app->request->headers['Access-Control-Request-Headers'])) {
                $cors['Access-Control-Allow-Headers'] = \Yii::$app->request->headers['Access-Control-Request-Headers'];
            }
            if (isset(\Yii::$app->request->headers['Origin'])) {
                $cors['Access-Control-Allow-Origin'] = \Yii::$app->request->headers['Origin'];
            }

            foreach ($cors  as $k => $c) {
                \Yii::$app->getResponse()->getHeaders()->set($k, $c);
            }
            \Yii::$app->end();
            return false;
        }
        return true;
    }
}