<?php


namespace app\modules\lk\controllers;


use app\models\api\CorsTrait;
use app\models\payonline\Partner;
use app\services\auth\AuthService;
use app\services\auth\models\User;
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

        if(!$this->getAuthService()->isCanRegUser($data['access_token'])) {
            throw new ForbiddenHttpException();
        }

        $partner = Partner::findOne(['Name' => $data['merchant_name']]);

        if(!$partner) {
            throw new BadRequestHttpException();
        }

        $user = new User();
        $user->PartnerId = $partner->ID;
        $user->Login = $data['login'];
        $user->Email = $data['email'];
        $user->PhoneNumber = $data['phone_number'];

        if($user->save()) {
            Yii::$app->response->statusCode = 201;
        } else {
            throw new BadRequestHttpException();
        }
    }

    /**
     * @return AuthService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getAuthService()
    {
        return Yii::$container->get('AuthService');
    }

}
