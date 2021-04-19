<?php

namespace app\models;

use app\models\api\Reguser;
use app\models\payonline\User;
use app\models\payonline\Userinfo;

class UserWgt
{
    /**
     * Поиск пользователя по IMEI или email
     * @param string $email
     * @param string $imei
     * @return int $IdUser
     */
    public function getId($email, $imei)
    {
        $IdUser = 0;

        if (!empty($email) && empty($imei)) {
            // вход с пк
            $userInfo = new Userinfo();
            $u = $userInfo->findUser($email);
            if ($u) {
                $IdUser = $u->ID;
            }

        } elseif (!empty($imei)) {
            // вход с мобильного
            $reguer = new Reguser();
            $u = $reguer->findUser($imei);
            if ($u) {
                $IdUser = $u->ID;
            }
        }

        return $IdUser;
    }

    /**
     * Получить карту (проверка)
     * @param $IdUser
     * @param $imei
     * @return false|null|string
     * @throws \yii\db\Exception
     */
    public function getKard($IdUser, $imei)
    {
        $reguer = new Reguser();
        $u = $reguer->findUser($imei);
        if ($u && $u->ID == $IdUser) {
            $card = $reguer->getCard($IdUser);
            if ($card) {
                return $card->ID;
            }
        }
        return false;
    }

    /**
     * User
     * @param int $IdUser
     * @return User
     */
    public function getUser($IdUser)
    {
        return User::findOne($IdUser);
    }
}