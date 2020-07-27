<?php


namespace app\services\auth\models;

/**
 * Class UserToken
 * @package app\services\auth\models
 * @property int $ID
 * @property int $UserId
 * @property string $IP
 * @property string $Token
 * @property string $RefreshToken
 * @property string $RoleNames
 * @property string $Scope
 * @property int $DateExpires
 * @property int $DateExpiresRefresh
 *
 * @property User $user
 */
class UserToken extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth_login_token';
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['ID' => 'UserId']);
    }

}
