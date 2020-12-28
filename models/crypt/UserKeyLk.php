<?php

namespace app\models\crypt;

use app\models\partner\PartnerUsers;
use app\models\payonline\Partner;
use Yii;
use yii\db\Exception;
use yii\web\IdentityInterface;
use yii\web\User;

class UserKeyLk implements IdentityInterface
{
    private $Key1Admin = 0;
    private $Key2Admin = 0;
    private $Key3Admin = 0;
    private $KeyChageAdmin = 0;
    private $DateChange = 0;
    private $AutoLockDate = 0;
    private $fio = "";
    private $IdUser = 0;
    /** @var null|PartnerUsers  */
    private $partnerModel = null;

    private $id = "";
    private $authKey = "";
    private $password = "";

    /**
     * Поиск пользователя по логину, для авторизации
     *
     * @param string $id Логин
     * @return UserKeyLk|IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        $keyUser = KeyUsers::find()
            ->where([
                'key_users.login' => $id,
                'key_users.IsDeleted' => 0,
                'key_users.IsActive' => 1
            ])
            ->one();
        //Максимальный срок неактивности пользователя 90 дней.
        if ($keyUser && $keyUser->DateLastLogin != 0 && $keyUser->DateLastLogin < time() - 90 * 86400) {
            self::LockUser($keyUser->ID, true);
        }

        if ($keyUser) {
            $user = new UserKeyLk();
            $user->id = $keyUser->Login;
            $user->fio = $keyUser->FIO;
            $user->password = $keyUser->Password;
            $user->IdUser = $keyUser->ID;
            $user->Key1Admin = $keyUser->Key1Admin;
            $user->Key2Admin = $keyUser->Key2Admin;
            $user->Key3Admin = $keyUser->Key3Admin;
            $user->KeyChageAdmin = $keyUser->KeyChageAdmin;
            $user->DateChange = $keyUser->DateChange;
            $user->AutoLockDate = $keyUser->DateChange;
            $user->partnerModel = $keyUser;

            return $user;
        }
        return null;
    }

    /**
     * Поиск по ID
     * @param int $id
     * @return UserKeyLk|null
     */
    public static function findIdentityId($id)
    {
        $keyUser = KeyUsers::find()
            ->where([
                'key_users.ID' => $id,
                'key_users.IsDeleted' => 0,
                'key_users.IsActive' => 1])
            ->one();
        if ($keyUser) {
            $user = new UserKeyLk();
            $user->id = $keyUser->Login;
            $user->fio = $keyUser->FIO;
            $user->password = $keyUser->Password;
            $user->IdUser = $keyUser->ID;
            $user->Key1Admin = $keyUser->Key1Admin;
            $user->Key2Admin = $keyUser->Key2Admin;
            $user->Key3Admin = $keyUser->Key3Admin;
            $user->KeyChageAdmin = $keyUser->KeyChageAdmin;
            $user->DateChange = $keyUser->DateChange;

            return $user;
        }
        return null;
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null; //static::findOne(['access_token' => $token]);
    }

    /**
     * Авторизация
     *
     * @param int $IdUser
     * @param string $login
     * @return void
     * @throws Exception
     */
    public static function Login($IdUser, $login)
    {
        self::ClearCntLogin();
        self::logAuth($IdUser, 1, $login);

        Yii::$app->session['KeyUser'] = $IdUser;
        Yii::$app->session['KeyUserSesTime'] = time();

        Yii::$app->db->createCommand()
            ->update('key_users', [
                'DateLastLogin' => time(),
                'AutoLockDate' => 0
            ], '`ID` = :ID', [':ID' => $IdUser])
            ->execute();
    }

    /**
     * Проверка времени сессии (15 минут)
     *
     * @return boolean
     */
    public static function CheckSesTime()
    {
        if (isset(Yii::$app->session['KeyUser'])) {
            if (Yii::$app->session['KeyUserSesTime'] > time() - 15 * 60) {
                //обновление сессии
                Yii::$app->session['KeyUserSesTime'] = time();
                return true;
            } else {
                //выход
                self::Logout();
                return false;
            }
        }
        return false;
    }

    /**
     * Выход
     */
    public static function Logout()
    {
        if (isset(Yii::$app->session['KeyUser'])) {
            self::logAuth(Yii::$app->session['KeyUser'], 9);
        }
        unset(Yii::$app->session['KeyUser']);
    }

    /**
     * Авторизован
     *
     * @return bool
     */
    public static function IsAuth()
    {
        return isset(Yii::$app->session['KeyUser']) && Yii::$app->session['KeyUser'] > 0;
    }

    /**
     * ID пользователя
     *
     * @return integer
     */
    public static function Id()
    {
        return isset(Yii::$app->session['KeyUser']) ? Yii::$app->session['KeyUser'] : 0;
    }

    /**
     * Проверка счетчика ошибок авторизации (6 ошибок, блок на 30 минут)
     *
     * @param int $IdUser
     * @return bool
     */
    public static function NotErrCntLogin($IdUser)
    {
        if ($IdUser) {
            $lockTimeMin = 30;
            $u = self::findIdentityId($IdUser);
            if ($u && !$u->AutoLockDate && $u->AutoLockDate < time() - $lockTimeMin * 60) {
                return (!isset(Yii::$app->session['errAtempt']) ||
                    (isset(Yii::$app->session['errAtempt']) && Yii::$app->session['errAtempt'] < 6) ||
                    (isset(Yii::$app->session['errTime']) && Yii::$app->session['errTime'] < time() - $lockTimeMin * 60));
            }
        }
        return false;
    }

    /**
     * Увеличение счетчика ошибок входа
     * @param int $IdUser
     * @param string $login
     */
    public static function IncCntLogin($IdUser, $login)
    {
        if (!isset(Yii::$app->session['errAtempt'])) {
            Yii::$app->session['errAtempt'] = 0;
        }
        Yii::$app->session['errAtempt'] += 1;
        Yii::$app->session['errTime'] = time();

        self::logAuth(0, 2, $login);

        if (isset(Yii::$app->session['errAtempt']) && Yii::$app->session['errAtempt'] > 6) {
            Yii::$app->db->createCommand()
                ->update('key_users', [
                    'AutoLockDate' => time()
                ], '`ID` = :ID', [':ID' => $IdUser])
                ->execute();
        }

    }

    /**
     * Сброс счетчика ошибок входа
     */
    public static function ClearCntLogin()
    {
        unset(Yii::$app->session['errAtempt']);
        unset(Yii::$app->session['errTime']);
    }

    /**
     * Необходимо обновление пароля (раз в 90 дней)
     *
     * @return bool
     */
    public static function NeedUpdatePw()
    {
        $u = self::findIdentityId(Yii::$app->session['KeyUser']);
        if ($u) {
            return $u->DateChange < time() - 90 * 86400;
        }
        return false;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === hash('sha256', $password);
    }

    /**
     * Доступ к ключу 1
     *
     * @return bool
     */
    public static function accessKey1()
    {
        $u = self::findIdentityId(Yii::$app->session['KeyUser']);
        if ($u) {
            return $u->Key1Admin;
        }
        return 0;
    }

    /**
     * Доступ к ключу 2
     *
     * @return bool
     */
    public static function accessKey2()
    {
        $u = self::findIdentityId(Yii::$app->session['KeyUser']);
        if ($u) {
            return $u->Key2Admin;
        }
        return 0;
    }

    /**
     * Доступ к ключу 3
     *
     * @return bool
     */
    public static function accessKey3()
    {
        $u = self::findIdentityId(Yii::$app->session['KeyUser']);
        if ($u) {
            return $u->Key3Admin;
        }
        return 0;
    }

    /**
     * Доступ к замене ключей
     *
     * @return bool
     */
    public static function accessKeyChange()
    {
        $u = self::findIdentityId(Yii::$app->session['KeyUser']);
        if ($u) {
            return $u->KeyChageAdmin;
        }
        return 0;
    }

    /**
     * @return PartnerUsers|null
     */
    public function getPartnerModel()
    {
        return $this->partnerModel;
    }

    public function getIdUser()
    {
        return $this->IdUser;
    }

    /**
     * ФИО пользователя
     * @return int
     */
    public function getFIO()
    {
        return $this->fio;
    }

    /**
     * Id пользователя
     * @param IdentityInterface $user
     * @return int
     */
    public static function getUserId($user)
    {
        $u = self::findIdentity($user->getId());
        if ($u) {
            return $u->getIdUser();
        }
        return null;
    }

    /**
     * ФИО по пользователю
     * @param IdentityInterface $user
     * @return string|null
     */
    public static function getUserFIO($user)
    {
        $u = self::findIdentity($user->getId());
        if ($u) {
            return $u->getFIO();
        }
        return null;
    }

    /**
     * Обновить пароль
     * @param $IdUser
     * @param string $passw
     * @throws Exception
     */
    public static function ChangePassw($IdUser, $passw)
    {
        Yii::$app->db->createCommand()
            ->update('key_users', [
                    'Password' => hash('sha256', $passw),
                    'DateChange' => time()
                ], '`ID` = :ID', [':ID' => $IdUser]
            )
            ->execute();

        self::logAuth($IdUser,3);
    }

    /**
     * Блокировка пользователя
     * @param $IdUser
     * @param boolean $lock - true - lock false - unlock
     * @throws Exception
     */
    public static function LockUser($IdUser, $lock)
    {
        Yii::$app->db->createCommand()
            ->update('key_users', [
                'IsActive' => !$lock
            ], '`ID` = :ID', [':ID' => $IdUser])
            ->execute();

        self::logAuth($IdUser,$lock ? 10 : 11);
    }

    /**
     * Лог входа в кабинет и замены ключей
     *
     * @param $IdUser
     * @param $type
     * @param $login
     */
    public static function logAuth($IdUser, $type, $login = '')
    {
        try {
            Yii::$app->db->createCommand()->insert('key_log', [
                'Date' => time(),
                'IdUser' => $IdUser,
                'Type' => $type,
                'IPLogin' => Yii::$app->request->getUserIP(),
                'DopInfo' => (!empty($login) ? ('Логин: ' . $login . ', ') : '') .mb_substr(Yii::$app->request->getUserAgent(), 0, 450)
            ])->execute();
            Yii::warning("Login: ". $IdUser . " IP=" . Yii::$app->request->getUserIP(). " Type=" . $type);

        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }

    }
}