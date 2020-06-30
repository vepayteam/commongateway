<?php

namespace app\modules\kfapi\controllers;

use app\models\bank\TCBank;
use app\models\kfapi\KfCheckreq;
use app\models\partner\UserLk;
use app\models\Payschets;
use app\models\TU;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * API Краутфандинга Web запросы
 */
class DefaultController extends Controller
{
    public $layout = '@app/views/layouts/communallayout';

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = '@app/views/layouts/swaggerlayout';
        return $this->render('@app/views/site/apidoc', ['url' => '/kfapi/default/swagger']);
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionBenificSwagger()
    {
        $this->layout = '@app/views/layouts/swaggerlayout';
        return $this->render('@app/views/site/apidoc', ['url' => '/kfapi/default/swagger-benific']);
    }

    /**
     * Renders the index view for the module
     * @return Response
     */
    public function actionSwagger()
    {
        return Yii::$app->response->sendFile(Yii::$app->basePath . '/doc/kf.yaml', '', ['inline' => true, 'mimeType' => 'application/yaml']);
    }

    /**
     * Renders the index view for the module
     * @return Response
     */
    public function actionSwaggerBenific()
    {
        return Yii::$app->response->sendFile(Yii::$app->basePath . '/doc/benific.yaml', '', ['inline' => true, 'mimeType' => 'application/yaml']);
    }

}
