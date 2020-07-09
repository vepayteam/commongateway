<?php


namespace app\modules\suppliers\controllers;

use app\models\api\CorsTrait;
use app\models\kfapi\KfRequest;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class BaseController extends Controller
{
    use CorsTrait;
    protected $body;
    protected $partnerId;


    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if ($this->checkBeforeAction()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;

            $body = Yii::$app->request->getRawBody();


            $kf = new KfRequest();
            $kf->CheckAuth(Yii::$app->request->headers, $body, 0);
            $this->partnerId = Yii::$app->request->headers['x-login'];

            try {
                $this->body = json_decode($body, true);
            } catch (\Exception $e) {
                throw new BadRequestHttpException($e->getMessage());
            }

            return parent::beforeAction($action);
        }
        return false;
    }

}
