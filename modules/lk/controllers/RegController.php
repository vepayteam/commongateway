<?php


namespace app\modules\lk\controllers;


use app\models\api\CorsTrait;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class RegController extends Controller
{
    const ROLE_CAN_CREATE_USER = 'php_account_admin';

    public $layout = 'default';
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
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if ($this->checkBeforeAction()) {
            $this->enableCsrfValidation = false;
            return parent::beforeAction($action);
        }
        return false;
    }

    protected function verbs()
    {
        return [
            'create' => ['POST'],
        ];
    }

    public function actionCreate()
    {
        $data = json_decode(Yii::$app->request->rawBody, true);


    }

    protected function authCanCreateUser()
    {
        $headers = Yii::$app->request->headers;

        if(!array_key_exists('Authorization', $headers)) {
            return false;
        }

        $token = explode(' ', $headers['Authorization'])[1];

    }


}
