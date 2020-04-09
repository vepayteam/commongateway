<?php

namespace app\modules\mfo\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Testclient controller for the `mfo` module
 */
class TestcliController extends Controller
{
    public $layout = '@app/views/layouts/communallayout';

    public function actionIndex()
    {
        return $this->render('index');
    }
}