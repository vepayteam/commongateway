<?php


namespace app\services\auth;


use app\services\auth\clients\ValidateTokenClient;
use app\services\auth\models\LoginForm;
use app\services\auth\models\RefreshForm;
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
    /** @var AMQPStreamConnection $connection */
    private $connection;

    public function getConnection()
    {
        if(is_null($this->connection)) {
            $this->connection = new AMQPStreamConnection(
                'localhost',
                5672,
                'guest',
                'guest'
            );
        }
        return $this->connection;
    }

    public function login(LoginForm $loginForm)
    {
        $loginClient = new LoginClient();
        $response = $loginClient->call($loginForm);

        $user = $this->getOrCreateUserByLogin($loginForm->login);

        $result = $response['result'];
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
        Yii::$app->session->set('authToken', $result['access_token']);

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
        $result = $response['result'];

        $token->Token = $result['access_token'];
        $token->RefreshToken = $result['refresh_token'];
        $token->RoleNames = json_encode($result['role_names']);
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
            ->andWhere(['<', 'DateExpires', time()])
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
            ->andWhere(['<', 'DateExpiresRefresh', time()])
            ->orderBy('DateExpires DESC')
            ->one();
    }

    /**
     * @param string $token
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function validateToken($token)
    {
        $validateTokenFrom = new ValidateTokenForm();
        $validateTokenFrom->token = $token;

        if(!$validateTokenFrom->validate()) {
            return false;
        }

        $validateTokenClient = new ValidateTokenClient();
        $response = $validateTokenClient->call($validateTokenFrom);

        if($response['status'] == 200) {
            return true;
        } else {
            return false;
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
