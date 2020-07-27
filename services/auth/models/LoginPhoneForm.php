<?php


namespace app\services\auth\models;


use app\services\auth\rpc_clients\LoginClient;
use yii\base\Model;

class LoginForm extends Model implements IClientForm
{
    public $login;
    public $password;

    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
        ];
    }

    public function asArray()
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
        ];
    }

}
