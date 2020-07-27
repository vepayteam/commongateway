<?php


namespace app\modules\lk\controllers;


use app\models\api\CorsTrait;
use app\services\auth\AuthService;
use app\services\auth\models\LoginForm;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class LoginController extends Controller
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

    public function actionIn()
    {
        $loginForm = new LoginForm();

        if (Yii::$app->request->isPost) {
            if(!$loginForm->load(Yii::$app->request->post()) || !$loginForm->validate()) {
                return $this->render('in', [
                    'loginForm' => $loginForm
                ]);
            }

            if($this->getAuthService()->login($loginForm)) {
                return $this->redirect('/lk/main/');
            }
        }

        return $this->render('in', [
            'loginForm' => $loginForm
        ]);
    }


    public function actionInByPhone()
    {
        $loginForm = new LoginForm();

        if (Yii::$app->request->isPost) {
            if(!$loginForm->load(Yii::$app->request->post()) || !$loginForm->validate()) {
                return $this->render('in', compact($loginForm));
            }
            return $this->inByPhonePost();
        }

        return $this->render('in-by-phone', compact($loginForm));

    }

    private function inByPhonePost()
    {

    }

    public function actionReg()
    {
        return $this->render('reg');
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
