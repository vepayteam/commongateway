<?php

namespace app\models\api;

use app\models\payonline\Cards;
use app\models\payonline\User;
use Yii;

class Reguser
{
    /**
     * Поиск пользователя или регистрация нового
     * @param string $imei
     * @param string $extuser
     * @param string $extpw
     * @param int $extorg
     * @param bool $ismobile
     * @return User|false
     */
    public function findUser($imei, $extuser = '', $extpw = '', $extorg = 0, $ismobile = true)
    {
        if (!empty($extuser)) {
            $User = User::findOne(['Login' => $extuser, 'ExtOrg' => $extorg, 'IsDeleted' => 0]);
            if ($User && $User->Password == $extpw) {
                return $User;
            } elseif (!$User) {
                $User = new User();
                $User->Login = $extuser;
                $User->Password = $extpw;
                $User->ExtOrg = $extorg;
                $User->DateRegister = time();
                $User->ExtCustomerIDP = md5($extuser.'-'.$extpw.'-'.$extorg);
                if ($ismobile) {
                    $User->UserDeviceType = mb_stripos(Yii::$app->request->headers->get('HTTP_USER_AGENT'), "iphone") == false ? 2 : 3;
                } else {
                    $User->UserDeviceType = 0;
                }
                $User->save(false);

                return $User;
            } else {
                return false;
            }
        } else {
            $User = User::findOne(['IMEI' => $imei, 'IsDeleted' => 0]);
            if ($User) {
                return $User;
            } elseif (mb_strlen($imei) > 10) {
                $User = new User();
                $User->IMEI = $imei;
                $User->DateRegister = time();
                $User->ExtCustomerIDP = md5($imei);
                $User->UserDeviceType = mb_stripos(Yii::$app->request->headers->get('HTTP_USER_AGENT'), "iphone") == false ? 2 : 3;
                $User->save(false);

                return $User;
            } else {
                return false;
            }
        }
    }

    /**
     * Привязанная карта
     * @param int $IdUser
     * @return Cards|null
     */
    public function getCard($IdUser)
    {
        $card = Cards::findOne(['IdUser' => $IdUser,  'TypeCard' => 0, 'IsDeleted' => 0]);
        if ($card &&
                mktime(
                    0, 0, 0,
                    mb_substr($card->SrokKard, 0, mb_strlen($card->SrokKard) == 4 ? 2 : 1),
                    28,
                    mb_substr($card->SrokKard, -2, 2)
                ) < time()) {
            $card->IsDeleted = 1;
            $card->save(false);
            return null;
        }
        return $card;
    }

    /**
     * Обновление ФИО
     * @param User $User
     * @param $Fam
     * @param $Name
     * @param $Otch
     */
    public function UpdateFio(User $User, $Fam, $Name, $Otch)
    {
        if ($User) {
            if (!empty($Name)) {
                $User->Name = $Name;
            }
            if (!empty($Fam)) {
                $User->Fam = $Fam;
            }
            if (!empty($Otch)) {
                $User->Otch = $Otch;
            }
            $User->save(false);
        }
    }
}