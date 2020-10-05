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

class RegClient extends BaseClient
{
    protected $uri = '/add_account';

    public function call(IClientForm $regForm)
    {
        $data = $regForm->asArray();
        $response = $this->sendRequest($data);

        if($response && array_key_exists('result', $response)) {
            return $response;
        }

        throw new BadRequestHttpException('Ошибка регистрации');
    }
}
