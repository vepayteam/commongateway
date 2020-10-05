<?php


namespace app\services\auth\clients;


use app\services\auth\AuthService;
use app\services\auth\models\IClientForm;
use app\services\auth\models\LoginForm;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\base\Model;
use yii\web\BadRequestHttpException;

class RefreshTokenClient extends BaseClient
{
    protected $uri = '/refresh_token';

    public function call(IClientForm $refreshForm)
    {
        $data = $refreshForm->asArray();
        $response = $this->sendRequest($data);

        if($response) {
            return $response;
        }

        throw new BadRequestHttpException('Ошибка авторизации');
    }



}
