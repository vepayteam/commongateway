<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

use app\services\card\Reg;
use app\services\card\Del;
use app\services\card\Get;
use app\services\card\Info;


class CardController extends Controller
{
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
     */
    public function beforeAction($action)
    {
        if ($this->checkBeforeAction()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;
            return parent::beforeAction($action);
        }
        return false;
    }

    protected function verbs()
    {
        return [
            'info' => ['POST'],
            'reg' => ['POST'],
            'get' => ['POST'],
            'del' => ['POST']
        ];
    }

    /**
     * @return Response
     */
    public function actionReg()
    {
        $registration = Reg::exec();
        return $this->asJson($registration->response);
    }

    /**
     * @return Response
     */
    public function actionDel()
    {
        $registration = Del::exec();
        return $this->asJson($registration->response);
    }

    /**
     * @return Response
     */
    public function actionGet()
    {
        $registration = Get::exec();
        return $this->asJson($registration->response);
    }

    /**
     * @return Response
     */
    public function actionInfo()
    {
        $registration = Info::exec();
        return $this->asJson($registration->response);
    }

}
