<?php

namespace app\modules\keymodule\controllers;

use app\models\crypt\KeyUsers;
use app\models\crypt\UserKeyLk;
use app\models\partner\PartnerUsers;
use app\models\SendEmail;
use app\services\auth\TwoFactorAuthService;
use Yii;
use yii\base\Action;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * Default controller for the `keymodule` module
 */
class DefaultController extends Controller
{
    /**
     * @param Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!in_array($action->id, ['index', 'login', 'logout'])) {
            $res = UserKeyLk::CheckSesTime();
            if (!$res) {
                $this->redirect('/keymodule');
                return true;
            }
        }

        if (!in_array($action->id, ['chngpassw', 'changepw', 'logout'])) {
            if (UserKeyLk::NeedUpdatePw()) {
                $this->redirect('/keymodule/default/chngpassw');
                return true;
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * Главная
     *
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::CheckSesTime()) {
            return $this->render('index');
        } else {
            return $this->render('login');
        }
    }

    /**
     * Авторизация
     *
     * @return array|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionLogin()
    {
        if (!UserKeyLk::IsAuth()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                $login = Yii::$app->request->post('login');
                $password = Yii::$app->request->post('passw');
                $token = Yii::$app->request->post('token');
                $user = UserKeyLk::findIdentity($login);
                if ($user && $user->validatePassword($password) && UserKeyLk::NotErrCntLogin($user->getIdUser())) {
                    $twoFAService = new TwoFactorAuthService($user);
                    $responseStatus = 4;

                    if (!empty($token)) {
                        if ($twoFAService->validateToken($token)) {
                            //успех
                            UserKeyLk::Login($user->getIdUser(), $login);
                            $responseStatus = 1;
                        } else {
                            if ($twoFAService->sendToken()) {
                                $responseStatus = 2;
                            }
                        }
                        return ['status' => $responseStatus];
                    } else {
                        if ($twoFAService->sendToken()) {
                            $responseStatus = 2;
                        }
                    }
                    return ['status' => $responseStatus];
                } else {
                    if ($user) {
                        //ошибка пароля или блок
                        UserKeyLk::IncCntLogin($user->getIdUser(), $login);
                    } else {
                        //неверный пользователь
                        UserKeyLk::IncCntLogin(0, $login);
                    }
                    return ['status' => 0];
                }
            }
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Выход
     *
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        if (UserKeyLk::IsAuth()) {
            UserKeyLk::Logout();
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Смена пароля
     *
     * @return string|\yii\web\Response
     */
    public function actionChngpassw()
    {
        if (UserKeyLk::IsAuth()) {
            return $this->render('chngpassw', ['user' => KeyUsers::findOne(UserKeyLk::Id())]);
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Смена пароля
     *
     * @return array|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionChangepw()
    {
        if (UserKeyLk::IsAuth()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                $user = UserKeyLk::findIdentityId(UserKeyLk::Id());
                $oldPw = Yii::$app->request->post('oldpassw');
                $newPw1 = Yii::$app->request->post('passw');
                $newPw2 = Yii::$app->request->post('passw2');
                //минимальная длина - 7 знаков, должен состоять из букв и цифр
                if ($user && $user->validatePassword($oldPw) &&
                    $oldPw != $newPw1 &&
                    strlen($newPw1) >= 7 &&
                    preg_match('/(?=.*[0-9])(?=.*[a-zA-Z])/ius', $newPw1) &&
                    $newPw1 == $newPw2
                ) {
                    UserKeyLk::ChangePassw($user->getIdUser(), $newPw1);
                    return ['status' => 1];
                }
                UserKeyLk::logAuth($user->getIdUser(),8);
                return ['status' => 0];
            } else {
                return $this->redirect('/keymodule/default/chngpassw');
            }
        }
        return $this->redirect('/keymodule');
    }
}
