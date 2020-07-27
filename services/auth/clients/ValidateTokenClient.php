<?php


namespace app\services\auth\clients;


use app\services\auth\models\IClientForm;
use yii\web\BadRequestHttpException;

class ValidateTokenClient extends BaseClient
{
    protected $uri = '/validate_token';

    function call(IClientForm $validateTokenForm)
    {
        $data = $validateTokenForm->asArray();
        $response = $this->sendRequest($data);

        if($response) {
            return $response;
        }

        throw new BadRequestHttpException('Ошибка авторизации');
    }
}
