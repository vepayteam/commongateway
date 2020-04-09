<?php

namespace app\modules\partner\controllers;

use app\models\partner\PartnerUsers;
use app\models\payonline\Partner;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\partner\UserLk;

/**
 * Default controller for the `partner` module
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'index'],
                        'allow' => true,
                        'roles' => ['?'], //анонимный
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->getResponse()->redirect(Url::toRoute('/partner'), 302)->send();
                            return false;
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            $news = [['TypeNews' => 0, 'HeaderNews' => 'Новый кабинет мерчанта', 'TextNews' => 'Новый кабинет мерчанта', 'DateNew' => time()]];
            return $this->render('index', ['news' => $news]);
        } else {
            return $this->render('login');
        }
    }

    /**
     * Авторизация в кабинете
     *
     * @return array|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionLogin()
    {
        if (Yii::$app->request->isAjax && Yii::$app->user->isGuest) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $id = Yii::$app->request->post('login');
            $password = Yii::$app->request->post('passw');
            $user = UserLk::findIdentity($id);
            if ($user && UserLk::IsNotLoginLock($user->getId()) && $user->validatePassword($password)) {
                UserLk::LogLogin($user->getId());
                Yii::$app->user->login($user, 1800);
                return ['status' => 1];
            } else {
                if ($user) {
                    UserLk::IncCntLogin($user->getId());
                } else {
                    UserLk::IncCntLogin(null);
                }
                return ['status' => 0];
            }
        } else {
            return $this->redirect('/partner/index');
        }
    }

    /**
     * Выход
     *
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        if (Yii::$app->user) {
            Yii::$app->user->logout();
        }
        return $this->redirect('/partner/index');
    }

    /**
     * Смена пароля
     *
     * @return string
     */
    public function actionChngpassw()
    {
        return $this->render('chngpassw', ['user' => PartnerUsers::findOne(UserLk::getUserId(Yii::$app->user))]);
    }

    /**
     * Смена пароля (AJAX)
     *
     * @return array|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionChangepw()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $user = UserLk::findIdentity(Yii::$app->user->getId());
            $oldPw = Yii::$app->request->post('oldpassw');
            $newPw1 = Yii::$app->request->post('passw');
            $newPw2 = Yii::$app->request->post('passw2');
            if ($user->validatePassword($oldPw) &&
                $oldPw != $newPw1 &&
                strlen($newPw1) >= 8 &&
                preg_match('/(?=.*[0-9])(?=.*[a-zA-Z])/ius', $newPw1) &&
                $newPw1 == $newPw2) {
                UserLk::changePassw($user, $newPw1);
                return ['status' => 1];
            }
            return ['status' => 0];
        } else {
            return $this->redirect('/partner/chngpassw');
        }
    }
}
