<?php


namespace app\services\auth\models;


use app\services\auth\rpc_clients\LoginClient;
use yii\base\Model;

class LoginForm extends Model implements IClientForm
{
    const BASIC_TYPE_LOGIN = 'login';
    const EMAIL_TYPE_LOGIN = 'email';
    const PHONE_TYPE_LOGIN = 'phone_number';

    public $login;
    public $password;
    public $typeLogin;

    public function rules()
    {
        return [
            [['login', 'password', 'typeLogin'], 'required'],
        ];
    }

    public function asArray()
    {
        return [
            $this->typeLogin => $this->login,
            'password' => $this->password,
        ];
    }

    /**
     * @param array $data
     * @param null $formName
     * @return bool|void
     */
    public function load($data, $formName = null)
    {
        parent::load($data, $formName);
        $emailPattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/iu';
        $phonePattern = '/^\+?[0-9]{10,11}$/iu';

        $this->typeLogin = self::BASIC_TYPE_LOGIN;
        if(preg_match($emailPattern, $this->login)) {
            $this->typeLogin = self::EMAIL_TYPE_LOGIN;
        } elseif (preg_match($phonePattern, $this->login)) {
            $this->typeLogin = self::PHONE_TYPE_LOGIN;
        }
        return true;
    }
}
