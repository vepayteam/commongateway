<?php


namespace app\modules\lk\controllers;


use yii\web\Controller;

abstract class DefaultController extends Controller
{

    public function beforeAction($action)
    {
        return parent::beforeAction($action);

    }

}
