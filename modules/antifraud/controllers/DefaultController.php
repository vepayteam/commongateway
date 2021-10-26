<?php

namespace app\modules\antifraud\controllers;

use app\models\antifraud\control_objects\FingerPrint;
use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action->id === 'register-tracking') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Сохраняет данные в бд. Здесь наичнается работа антифрода.
     */
    public function actionRegisterTracking()
    {
        $model = new FingerPrint();
        if ($model->load(Yii::$app->request->post(), '') && $model->validate()) {
            $model->saveHash();
        }
    }
}
