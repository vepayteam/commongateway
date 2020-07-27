<?php


namespace app\services\auth\models;


use yii\base\Model;

class ValidateTokenForm extends Model implements IClientForm
{
    public $token;

    public function rules()
    {
        return [
            ['token', 'required']
        ];
    }

    public function asArray()
    {
        return [
            'access_token' => $this->token,
        ];
    }
}
