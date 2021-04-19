<?php

namespace app\modules\partner\controllers;

use app\models\partner\news\News;
use app\models\partner\news\Newsread;
use app\models\partner\PartnerUsers;
use app\models\payonline\Partner;
use app\models\payonline\User;
use app\models\SendEmail;
use app\services\auth\TwoFactorAuthService;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\partner\UserLk;
use function Sodium\library_version_minor;

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
            $news = News::find()
                ->where(['IsDeleted' => 0])
                ->orderBy(['DateAdd' => SORT_DESC])->limit(5)
                ->all();
            return $this->render('index', [
                'news' => $news,
                'IsAdmin' => UserLk::IsAdmin(Yii::$app->user),
            ]);
        } else {
            return $this->render('login');
        }
    }

    /**
     * Renders the index view for the module
     * @return array|string
     */
    public function actionAlerts()
    {
        if (Yii::$app->request->isAjax && !Yii::$app->user->isGuest) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $news = News::find()
                ->where(['IsDeleted' => 0])
                ->orderBy(['DateAdd' => SORT_DESC])->limit(3)
                ->all();
            $alerts = News::GetAlerts($news, UserLk::getUserId(Yii::$app->user));
            return ['status' => 1, 'data' => $alerts];
        }
        return $this->redirect('/partner/index');
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
            $token = Yii::$app->request->post('token');
            $user = UserLk::findIdentity($id);
            if ($user && UserLk::IsNotLoginLock($user->getId()) && $user->validatePassword($password)) {
                if ($user->getIsAdmin()) {
                    $twoFAService = new TwoFactorAuthService($user);
                    $responseStatus = 4;
                    if (!empty($token)) {
                        if ($twoFAService->validateToken($token)) {
                            UserLk::LogLogin($user->getId());
                            Yii::$app->user->login($user, 1800);
                            $responseStatus = 1;
                        }
                    } else {
                        if ($twoFAService->sendToken()) {
                            $responseStatus = 2;
                        }
                    }
                    return ['status' => $responseStatus];
                } else {
                    UserLk::LogLogin($user->getId());
                    Yii::$app->user->login($user, 1800);
                    return ['status' => 1];
                }
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

    /**
     * Добавить новосить
     * @return int[]|\yii\web\Response
     */
    public function actionAddnews()
    {
        if (Yii::$app->request->isAjax && UserLk::IsAdmin(Yii::$app->user)) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $news = new News();
            $news->load(Yii::$app->request->post());
            $news->DateAdd = time();
            if ($news->validate()) {
                $news->save(false);
                return ['status' => 1];
            }
            return ['status' => 0];
        }
        return $this->redirect('/');
    }

    /**
     * Добавить новосить
     * @return int[]|\yii\web\Response
     */
    public function actionDelnews()
    {
        if (Yii::$app->request->isAjax && UserLk::IsAdmin(Yii::$app->user)) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $news = News::findOne(['ID' => (int)Yii::$app->request->post('id')]);
            if ($news) {
                $news->IsDeleted = 1;
                $news->save(false);
                return ['status' => 1];
            }
            return ['status' => 0];
        }
        return $this->redirect('/');
    }

}