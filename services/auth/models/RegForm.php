<?php


namespace app\services\auth\models;


use app\services\auth\clients\LoginClient;
use Yii;
use yii\base\Model;

class RegForm extends Model implements IClientForm
{
    public $superuserToken;
    public $login;
    public $password;
    public $email;

    public function rules()
    {
        return [
            [['login', 'password', 'email'], 'required'],
            ['superuserToken', 'string'],
            ['login', 'string', 'min' => 3, 'max' => 30],
            ['password', 'string', 'min' => 8, 'max' => 63],
            ['password', 'validatePassword'],
            ['email', 'email'],
        ];
    }

    public function validatePassword()
    {
        if(
            !preg_match('/[a-z]/', $this->password)
            || !preg_match('/[A-Z]/', $this->password)
            || !preg_match('/[0-9]/', $this->password)
        ) {
            $this->addError('password', 'Пароль должен содержать буквы нижнего регистра, буквы верхнего регистра и символ цифры.');
        }
        return true;
    }

    public function load($data, $formName = null)
    {
        if(!$this->refreshSuperuserToken()) {
            return false;
        }

        return parent::load($data, $formName);
    }

    public function asArray()
    {
        return [
            'access_token' => $this->superuserToken,
            'login' => $this->login,
            'password' => $this->password,
            'email' => $this->email,
        ];
    }

    private function refreshSuperuserToken()
    {
        $token = null;
        $loginForm = new LoginForm();
        $loginForm->typeLogin = LoginForm::BASIC_TYPE_LOGIN;
        $loginForm->login = Yii::$app->params['services']['accounts']['superuserLogin'];
        $loginForm->password = Yii::$app->params['services']['accounts']['superuserPassword'];

        $loginClient = new LoginClient();
        try {
            $response = $loginClient->call($loginForm);
            $result = $response['result'];
            $this->superuserToken = $result['access_token'];
        } catch (\Exception $e) {
            $this->addError('login', 'Ошибка регистрации');
            return false;
        }

        return true;
    }
}
