<?php


namespace app\modules\antifraud\controllers;

use app\models\antifraud\control_objects\FingerPrint;
use app\models\antifraud\partner\AntiFraudModel;
use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use app\modules\partner\controllers\SelectPartnerTrait;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;

class DefaultController extends Controller
{

    /**
     * Сохраняет данные в бд. Здесь наичнается работа антифрода.
     */
    public function actionRegisterTracking()
    {
        $this->enableCsrfValidation = false;
        $model = new FingerPrint();
        $data = Yii::$app->request->post();
        if ($model->load($data, '') && $model->validate()) {
            $model->saveHash();
        }
    }
}
