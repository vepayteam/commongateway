<?php


namespace app\modules\lk\controllers;


class DefaultController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
