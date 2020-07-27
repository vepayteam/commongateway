<?php


namespace app\services\auth\clients;


use app\services\auth\AuthService;
use app\services\auth\models\IClientForm;
use app\services\auth\models\LoginForm;
use PhpAmqpLib\Message\AMQPMessage;
use Requests;
use Yii;
use yii\base\Model;
use yii\web\BadRequestHttpException;

class LoginClient extends BaseClient
{
    protected $uri = '/login';

    public function call(IClientForm $loginForm)
    {
        $data = $loginForm->asArray();
        $response = $this->sendRequest($data);

        if($response) {
            return $response;
        }

        throw new BadRequestHttpException('Ошибка авторизации');
    }



}
