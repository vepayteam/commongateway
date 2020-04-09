<?php

namespace app\modules\keymodule\controllers;

use app\models\crypt\KeyUsers;
use app\models\crypt\LogKeyUser;
use app\models\crypt\UserKeyLk;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class UsersController extends Controller
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
            $data = KeyUsers::findAll(['IsDeleted' => 0]);
            return $this->render('index', ['data' => $data]);
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Блокировка пользователя
     *
     * @param $id
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionLock($id)
    {
        if (UserKeyLk::IsAuth()) {
            UserKeyLk::LockUser($id, true);
            return $this->redirect('/keymodule/users');
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Разблокировка пользователя
     *
     * @param $id
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionUnlock($id)
    {
        if (UserKeyLk::IsAuth()) {
            UserKeyLk::LockUser($id, false);
            return $this->redirect('/keymodule/users');
        }
        return $this->redirect('/keymodule');
    }
}