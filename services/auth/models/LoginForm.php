<?php


namespace app\services\auth\models;


use app\services\auth\rpc_clients\LoginClient;
use yii\base\Model;

class LoginForm extends Model
{
    private $email;
    private $password;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
        ];
    }

    public function validatePassword()
    {


    }

    public function login()
    {
        $loginClient = new LoginClient();
        $loginClient->call($this);
    }


    public function asArray()
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

}
