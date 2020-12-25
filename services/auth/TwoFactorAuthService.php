<?php
namespace app\services\auth;

use app\models\crypt\UserKeyLk;
use app\models\partner\UserLk;
use app\models\SendEmail;
use Yii;
use yii\db\Query;

/**
 * Class TwoFactorAuthService
 * @package app\services\auth
 */
class TwoFactorAuthService
{
    /** @var UserLk */
    private $user;

    /** @var array */
    private $lastToken;

    /** @var string */
    private $token_table = 'user_token';

    /**
     * TwoFactorAuthService constructor.
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
        if ($this->user instanceof UserLk) {
            $this->token_table = 'user_token';
        }
        if ($this->user instanceof UserKeyLk) {
            $this->token_table = 'key_users_token';
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function sendToken()
    {
        if ($token = $this->generateToken()) {
            return $this->sendTokenInternal($token['token']);
        }
        return false;
    }

    /**
     * валидирует переданное значение токена
     * @param $token
     * @return bool
     * @throws \yii\db\Exception
     */
    public function validateToken($token)
    {
        $currentToken = $this->getLastToken();
        if (empty($currentToken)) {
            return false;
        }

        if ($this->tokenIsInactive($currentToken)) {
            return false;
        }

        if ($token === $currentToken['token']) {
            Yii::$app->db->createCommand()->delete($this->token_table, ['id' => $currentToken['id']])->execute();
            return true;
        }
        return false;
    }

    /**
     * @return array|bool
     */
    private function getLastToken()
    {
        if (empty($this->lastToken)) {
            $this->lastToken = (new Query())
                ->select(['id', 'token', 'valid_until'])
                ->from($this->token_table)
                ->where(['user_id' => $this->user->getIdUser()])
                ->one();
        }
        return $this->lastToken;
    }

    /**
     * @param $token
     * @return bool
     */
    private function tokenIsInactive($token)
    {
        return time() >= strtotime($token['valid_until']);
    }

    /**
     * @return array|null
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    private function generateToken()
    {
        $lastToken = $this->getLastToken();

        if (!empty($lastToken)) {
            if ($this->tokenIsInactive($lastToken)) {
                //если есть токен, и он протух, его надо создать
                Yii::$app->db->createCommand()->delete($this->token_table, ['id' => $lastToken['id']])->execute();
            } else {
                //если токен актуален, то ничего не делаем
                return $lastToken;
            }
        }

        $this->lastToken = [
            'user_id' => $this->user->getIdUser(),
            'token' => Yii::$app->security->generateRandomString(),
            'created_at' => date('Y-m-d H:i:s'),
            'valid_until' => date('Y-m-d H:i:s', time() + Yii::$app->params['login_user_token_valid_time']),
        ];

        if (Yii::$app->db->createCommand()->insert($this->token_table, $this->lastToken)->execute()) {
            return $this->lastToken;
        }
        return null;
    }

    /**
     * @param $token
     * @return bool
     */
    private function sendTokenInternal($token)
    {
        $subj = 'Подтверждение входа через email';
        $content = 'Код для подтверждения входа пользователя ' . $this->user->getId() . ':<br>';
        $content .= $token;

        return (new SendEmail())->send(
            $this->user->getPartnerModel()->Email,
            'robot@vepay.online',
            $subj,
            $content
        );
    }


}