<?php


namespace app\modules\lk\controllers;


use app\models\api\CorsTrait;
use app\models\partner\UserLk;
use app\services\auth\AuthService;
use app\services\auth\models\User;
use app\services\auth\models\UserToken;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

abstract class BaseController extends Controller
{

    public $layout = 'default';
    use CorsTrait;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $this->updateBehaviorsCors($behaviors);
        return $behaviors;
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if ($this->checkBeforeAction()) {
            $this->enableCsrfValidation = false;

            // Если активна старая авторизация, убиваем ее
            if(!Yii::$app->user->isGuest && Yii::$app->user->identity instanceof UserLk) {
                Yii::$app->user->logout();
            }

            if(!$this->checkAuth()) {
                $this->redirect('/lk/login/in');
                return false;
            }
            return parent::beforeAction($action);
        }
        return false;
    }

    protected function verbs()
    {
        return [
            'in' => ['POST'],
        ];
    }

    /**
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function checkAuth()
    {
        if(!Yii::$app->session->get(AuthService::TOKEN_SESSION_KEY)) {
            return false;
        }

        $token = Yii::$app->session->get(AuthService::TOKEN_SESSION_KEY);
        if(!$this->getAuthService()->checkIsActualToken($token)) {
            if(!($userToken = $this->getAuthService()->checkIsCanRefreshToken($token))) {
                return false;
            }

            $userToken = $this->getAuthService()->refreshToken($userToken);
            if($userToken === false) {
                return false;
            }

            Yii::$app->session->set(AuthService::TOKEN_SESSION_KEY, $userToken->Token);
            $token = $userToken->Token;
        }

        $userToken = UserToken::findOne(['Token' => $token]);
        if($userToken && $this->getAuthService()->validateToken($token)) {
            Yii::$app->user->login($userToken->user);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return AuthService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getAuthService()
    {
        return Yii::$container->get('AuthService');
    }

}
