<?php


namespace app\services\auth;


use app\services\auth\clients\RegClient;
use app\services\auth\clients\ValidateTokenClient;
use app\services\auth\models\LoginForm;
use app\services\auth\models\RefreshForm;
use app\services\auth\models\RegForm;
use app\services\auth\models\User;
use app\services\auth\models\UserToken;
use app\services\auth\clients\LoginClient;
use app\services\auth\clients\RefreshTokenClient;
use app\services\auth\models\ValidateTokenForm;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Yii;
use yii\base\Model;

class AuthService
{
    const TOKEN_SESSION_KEY = 'authToken';

    public function reg(RegForm $regForm)
    {
        $regClient = new RegClient();
        try {
            $response = $regClient->call($regForm);
            $result = $response['result'];
        } catch (\Exception $e) {
            $regForm->addError('login', 'Ошибка регистрации');
            return false;
        }

        if(!array_key_exists('result', $response)) {
            $regForm->addError('login', 'Ошибка регистрации');
            return false;
        }
        return true;
    }

    public function isCanRegUser($token)
    {
        $validateTokenForm = new ValidateTokenForm();
        $validateTokenForm->token = $token;

        $validateTokenClient = new ValidateTokenClient();
        try {
            $response = $validateTokenClient->call($validateTokenForm);
            $result = $response['result'];
            return in_array(Yii::$app->params['services']['accounts']['canRegUserRole'], $result['role_names']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function login(LoginForm $loginForm)
    {
        $loginClient = new LoginClient();
        try {
            $response = $loginClient->call($loginForm);
            $result = $response['result'];
        } catch (\Exception $e) {
            $loginForm->addError('login', 'Неправильный логин или пароль');
            return false;
        }

        $user = $this->getOrCreateUserByLogin($loginForm->login);

        $token = new UserToken();
        $token->UserId = $user->ID;
        $token->IP = Yii::$app->request->remoteIP;
        $token->Token = $result['access_token'];
        $token->RefreshToken = $result['refresh_token'];
        $token->RoleNames = json_encode($result['role_names']);
        $token->Scope = '[]';
        $token->DateExpires = time() + $result['access_token_expires_in'];
        $token->DateExpiresRefresh = time() + $result['refresh_token_expires_in'];
        $token->save();
        Yii::$app->user->login($user);
        Yii::$app->session->set(AuthService::TOKEN_SESSION_KEY, $result['access_token']);

        return true;
    }

    public function logout()
    {
        if(!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        Yii::$app->session->set(AuthService::TOKEN_SESSION_KEY, null);
        return true;
    }

    public function refreshToken(UserToken $token)
    {
        $refreshForm = new RefreshForm();
        $refreshForm->refreshToken = $token->RefreshToken;

        if(!$refreshForm->validate()) {
            return false;
        }

        $refreshClient = new RefreshTokenClient();
        $response = $refreshClient->call($refreshForm);

        if(!array_key_exists('result', $response)) {
            return false;
        }

        $result = $response['result'];

        $token->Token = $result['access_token'];
        $token->RefreshToken = $result['refresh_token'];
        $token->Scope = '[]';
        $token->DateExpires = time() + $result['access_token_expires_in'];
        $token->DateExpiresRefresh = time() + $result['refresh_token_expires_in'];
        $token->save();

        return $token;
    }

    /**
     * @param string $token
     * @return UserToken|null
     */
    public function checkIsActualToken($token)
    {
        return UserToken::find()
            ->andWhere(['=', 'Token', $token])
            ->andWhere(['=', 'IP', Yii::$app->request->remoteIP])
            ->andWhere(['>', 'DateExpires', time()])
            ->orderBy('DateExpires DESC')
            ->one();
    }

    /**
     * @param string $token
     * @return UserToken|null
     */
    public function checkIsCanRefreshToken($token)
    {
        return UserToken::find()
            ->andWhere(['=', 'Token', $token])
            ->andWhere(['=', 'IP', Yii::$app->request->remoteIP])
            ->andWhere(['>', 'DateExpiresRefresh', time()])
            ->orderBy('DateExpires DESC')
            ->one();
    }


    /**
     * @param $token
     * @return array|null
     * @throws \yii\web\BadRequestHttpException
     */
    public function validateToken($token)
    {
        $validateTokenFrom = new ValidateTokenForm();
        $validateTokenFrom->token = $token;

        if(!$validateTokenFrom->validate()) {
            return null;
        }

        $validateTokenClient = new ValidateTokenClient();
        $response = $validateTokenClient->call($validateTokenFrom);

        if($response['status'] == 200) {
            return $response['result'];
        } else {
            return null;
        }
    }

    private function getOrCreateUserByLogin($login)
    {
        $user = User::findOne(['Login' => $login]);
        if(!$user) {
            $user = new User();
            $user->Login = $login;
            $user->save();
        }
        return $user;
    }

}
