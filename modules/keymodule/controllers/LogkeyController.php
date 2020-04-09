<?php

namespace app\modules\keymodule\controllers;

use app\models\crypt\LogKeyUser;
use app\models\crypt\UserKeyLk;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class LogkeyController extends Controller
{

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $res = UserKeyLk::CheckSesTime();
        if (!$res) {
            $this->redirect('/keymodule');
            return true;
        }

        if (UserKeyLk::NeedUpdatePw()) {
            $this->redirect('/keymodule/default/chngpassw');
            return true;
        }

        return parent::beforeAction($action);
    }

    /**
     * Раздел настройки ключей
     *
     * @return string
     */
    public function actionIndex()
    {
        if (UserKeyLk::IsAuth()) {
            return $this->render('index');
        }
        return $this->redirect('/keymodule');
    }

    public function actionList()
    {
        if (UserKeyLk::IsAuth()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $LogKeyUser = new LogKeyUser();
                $LogKeyUser->load(Yii::$app->request->post(), '');
                if ($LogKeyUser->validate()) {
                    return ['status' => 1, 'data' => $this->renderPartial('_list', ['data' => $LogKeyUser->GetList()])];
                } else {
                    return ['status' => 0, 'message' => 'Ошибка'];
                }
            }
        }
        return $this->redirect('/keymodule');
    }


}