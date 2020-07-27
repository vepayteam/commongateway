<?php


namespace app\services\auth\models;


use app\services\auth\AuthService;
use Yii;
use yii\web\IdentityInterface;

/**
 * Class User
 * @package app\services\auth\models
 * @property int $ID
 * @property string $Email
 * @property string $Login
 * @property string $PhoneNumber
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{

    public static function tableName()
    {
        return 'auth_logins';
    }

    public static function findIdentity($id)
    {
        return self::findOne(['Id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return UserToken::findOne(['Token' => $token])->user;
    }

    public function getId()
    {
        return $this->ID;
    }

    public function getAuthKey()
    {
        $ip = Yii::$app->request->remoteIP;
        $token = $this->getTokens()
            ->andWhere(['=', 'IP', $ip])
            ->andWhere(['>', 'DateExpires', time()])
            ->orderBy('DateExpires DESK')
            ->one();

        if($token) {
            return $token->Token;
        } else {
            return null;
        }
    }

    public function validateAuthKey($authKey)
    {
        $ip = Yii::$app->request->remoteIP;
        $token = $this->getTokens()
            ->andWhere(['=', 'IP', $ip])
            ->andWhere(['=', 'Token', $authKey])
            ->andWhere(['>', 'DateExpires', time()])
            ->orderBy('DateExpires DESK')
            ->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(UserToken::className(), ['UserId' => 'ID']);
    }

    /**
     * @return UserToken|array|\yii\db\ActiveRecord|null
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getActualToken()
    {
        $token = $this->getTokens()
            ->andWhere(['=', 'IP', Yii::$app->request->remoteIP])
            ->andWhere(['>', 'DateExpires', time()])
            ->orderBy('DateExpires DESC')
            ->one();

        if(!$token) {
            /** @var UserToken $token **/
            $token = $this->getTokens()
                ->andWhere(['=', 'IP', Yii::$app->request->remoteIP])
                ->andWhere(['>', 'DateExpiresRefresh', time()])
                ->orderBy('DateExpiresRefresh DESC')
                ->one();

            if(!$token) {
                return null;
            }
            $this->getAuthService()->refreshToken($token);

        }
        return $token;
    }

    /**
     * @return AuthService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getAuthService()
    {
        return Yii::$container->get('AuthService');
    }
}
