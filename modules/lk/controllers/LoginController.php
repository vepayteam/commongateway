<?php


namespace app\modules\lk\controllers;


use app\services\auth\models\LoginForm;
use Yii;
use yii\web\Controller;

class LoginController extends Controller
{
    public $layout = 'default';

    public function actionIn()
    {
        $loginForm = new LoginForm();

        if (Yii::$app->request->isPost) {
            if(!$loginForm->load(Yii::$app->request->post()) || !$loginForm->validate()) {
                return $this->render('in', compact($loginForm));
            }
            return $this->inPost();
        }

        return $this->render('in', compact($loginForm));
    }

    private function inPost()
    {


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

}
