<?php

namespace app\models\partner;

use app\models\payonline\Partner;
use Yii;
use yii\db\Exception;
use yii\web\IdentityInterface;
use yii\web\User;

class UserLk implements IdentityInterface
{
    const ROLE_ADMIN = "admin";

    private $isAdmin = false;
    private $roleUser = 0;
    private $partner = 0;
    /** @var null|PartnerUsers  */
    private $partnerModel = null;
    private $fio = "";
    private $IdUser = 0;

    private $id = "";
    private $authKey = "";
    private $password = "";

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return UserLk|IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     * @return UserLk
     */
    public static function findIdentity($id)
    {
        /** @var PartnerUsers $partner */
        $partner = PartnerUsers::find()
            ->where(['partner_users.login' => $id, 'partner_users.IsDeleted' => 0, 'partner_users.IsActive' => 1])
            ->leftJoin('partner', 'partner.ID = partner_users.IdPartner AND partner.IsDeleted = 0 AND partner.IsBlocked = 0')
            ->andWhere(['or', 'partner.ID > 0', 'partner_users.IsAdmin = 1'])
            ->one();
        if ($partner) {
            $user = new UserLk();
            $user->id = $partner->Login;
            $user->fio = $partner->FIO;
            $user->password = $partner->Password;
            $user->isAdmin = $partner->IsAdmin;
            $user->roleUser = $partner->RoleUser;
            $user->partner = $partner->IdPartner;
            $user->IdUser = $partner->ID;
            $user->partnerModel = $partner;

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
     * Это админ
     * @return bool
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * ИД партнера
     * @return int
     */
    public function getPartner()
    {
        return $this->partner;
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
     * Роль пользователя
     * @return int
     */
    public function getRoleUser()
    {
        return $this->roleUser;
    }

    /**
     * Проверка что это администратор
     * @param IdentityInterface $user
     * @return bool
     */
    public static function IsAdmin($user)
    {
        $u = UserLk::findIdentity($user->getId());
        if ($u) {
            return $u->getIsAdmin();
        }
        return null;
    }

    /**
     * Кабинет МФО
     * @param IdentityInterface $user
     * @return bool
     */
    public static function IsMfo($user)
    {
        $u = UserLk::findIdentity($user->getId());
        if ($u) {
            $partner = Partner::findOne($u->getPartner());
            if ($partner) {
                return $partner->IsMfo;
            }
        }
        return null;
    }

    /**
     * Id пользователя
     * @param IdentityInterface $user
     * @return int
     */
    public static function getUserId($user)
    {
        $u = UserLk::findIdentity($user->getId());
        if ($u) {
            return $u->getIdUser();
        }
        return null;
    }

    /**
     * Id Партнера по пользователю
     * @param IdentityInterface $user
     * @return int
     */
    public static function getPartnerId($user)
    {
        $u = UserLk::findIdentity($user->getId());
        if ($u) {
            return $u->getPartner();
        }
        return null;
    }

    /**
     * Partner по пользователю
     * @param IdentityInterface $user
     * @return Partner|null
     */
    public static function getPart($user)
    {
        $u = UserLk::findIdentity($user->getId());
        if ($u) {
            $partner = Partner::findOne($u->getPartner());
            return $partner;
        }
        return null;
    }

    /**
     * Id Партнера по пользователю
     * @param IdentityInterface $user
     * @return string|null
     */
    public static function getUserFIO($user)
    {
        $u = UserLk::findIdentity($user->getId());
        if ($u) {
            return $u->getFIO();
        }
        return null;
    }
    /**
     * Роль пользователя
     * @param IdentityInterface $user
     * @return int
     */
    public static function getUserRole($user)
    {
        $u = UserLk::findIdentity($user->getId());
        if ($u) {
            return $u->getRoleUser();
        }
        return null;
    }

    /**
     * Разделы пользователя
     * @param IdentityInterface $user
     * @return array|null
     */
    public static function getRazdels($user)
    {
        $u = UserLk::findIdentity($user->getId());
        if ($u) {
            $rz = [];
             $res = PartUserAccess::find()->select('IdRazdel')->where(['IdUser' => $u->getIdUser()])->all();
             foreach ($res as $row) {
                 $rz[$row->IdRazdel] = $row->IdRazdel;
             }
             return $rz;
        }
        return null;
    }


    /**
     * Обновить пароль
     * @param UserLk $user
     * @param string $passw
     * @throws \yii\db\Exception
     */
    public static function changePassw($user, $passw)
    {
        Yii::$app->db->createCommand()
            ->update('partner_users', [
                    'Password' => hash('sha256', $passw)
                ], 'Login = :LOGIN', [':LOGIN' => $user->id]
            )
            ->execute();
    }

    /**
     * Лог входа в кабинет
     *
     * @param $Login
     * @param $type
     */
    public static function logAuth($Login, $type)
    {
        $user = !empty($Login) ? PartnerUsers::findOne(['Login' => $Login]) : null;
        Yii::$app->db->createCommand()->insert('loglogin', [
            'Date' => time(),
            'IdUser' => $user ? $user->ID : 0,
            'Type' => $type,
            'IPLogin' => Yii::$app->request->getUserIP(),
            'DopInfo' => mb_substr(Yii::$app->request->getUserAgent(), 0, 450)
        ])->execute();

        Yii::warning("Login partner: ". $Login . " IP=" . Yii::$app->request->getUserIP(). " Type=" . $type);
    }

    /**
     * Увеличение счетчика ошибок входа
     * @param string $login
     */
    public static function IncCntLogin($login)
    {
        if (!isset(Yii::$app->session['errAtempt'])) {
            Yii::$app->session['errAtempt'] = 0;
        }
        Yii::$app->session['errAtempt'] += 1;
        Yii::$app->session['errTime'] = time();

        self::logAuth($login, 2);

        if (!empty($login)) {

            $user = PartnerUsers::findOne(['Login' => $login]);
            if ($user) {
                if ($user->ErrorLoginCnt > 10 - 1 && $user->DateErrorLogin > time() - 900) {
                    $user->AutoLockDate = time();
                } else {
                    $user->AutoLockDate = 0;
                }
                $user->ErrorLoginCnt++;
                $user->DateErrorLogin = time();
                $user->save(false);
            }
        }
    }

    /**
     * Авторизация
     *
     * @param string $login
     * @return void
     * @throws Exception
     */
    public static function LogLogin($login)
    {
        unset(Yii::$app->session['errAtempt']);
        unset(Yii::$app->session['errTime']);

        self::logAuth($login, 1);

        Yii::$app->db->createCommand()
            ->update('partner_users', [
                'DateLastLogin' => time(),
                'DateErrorLogin' => 0,
                'AutoLockDate' => 0,
                'ErrorLoginCnt' => 0,
            ], '`Login` = :LOGIN', [':LOGIN' => $login])
            ->execute();
    }

    /**
     * Проверка блокировки авторизации
     *
     * @param $login
     * @return bool
     */
    public static function IsNotLoginLock($login)
    {
        $notSesLock = (
            !isset(Yii::$app->session['errAtempt']) ||
            (isset(Yii::$app->session['errAtempt']) && Yii::$app->session['errAtempt'] < 10) ||
            (isset(Yii::$app->session['errTime']) && Yii::$app->session['errTime'] < time() - 900)
        );

        if ($notSesLock && $login) {
            $user = PartnerUsers::findOne(['Login' => $login]);
            return $user ? $user->AutoLockDate < time() - 900 : false;
        }

        return false;
    }

}