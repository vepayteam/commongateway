<?php

namespace app\modules\h2hapi\v1\controllers;

use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $this->layout = '@app/views/layouts/swaggerlayout';
        $this->view->params['title'] = "Host to Host API v.1";

        /** @todo Разобраться с роутингом, почему без слеша в конце URL не работает */
        return $this->render('@app/views/site/apidoc', ['url' => Url::to(['swagger']) . '/']);
    }

    /**
     * @return Response
     */
    public function actionSwagger(): Response
    {
        return \Yii::$app->response->sendFile(\Yii::$app->basePath . '/doc/h2h.yaml', '', [
            'inline' => true,
            'mimeType' => 'application/yaml',
        ]);
    }
}