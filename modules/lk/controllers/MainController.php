<?php


namespace app\modules\lk\controllers;


class MainController extends DefaultController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
