<?php


namespace app\modules\lk\controllers;


use app\services\auth\models\LoginForm;
use Yii;
use yii\web\Controller;

class LoginController extends Controller
{

    public function actionIn()
    {
        $loginForm = new LoginForm();

        if (Yii::$app->request->isPost) {
            if(!$loginForm->load(Yii::$app->request->post()) || !$loginForm->validate()) {
                return $this->render('in', compact($loginForm));
            }


            return $this->inPost();
        }

        $this->render('in', compact($loginForm));
    }

    private function inPost()
    {


    }

}
