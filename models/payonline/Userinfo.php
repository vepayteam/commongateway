<?php

namespace app\models\payonline;

use app\models\SendEmail;

class Userinfo
{
    private $User = null;

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * @param $Id
     * @return User|null
     */
    public function getUserById($Id)
    {
        $this->User = User::find()->where([
            'ID' => $Id,
            'IMEI' => '0',
            'IsDeleted' => 0
        ])->andWhere('Email IS NOT NULL')
            ->one();
        return $this->User;
    }

    /**
     * Регистрция пользователя
     * @param string $email
     * @param int $reg
     * @param string $fio
     * @param string $phone
     * @param bool $sendActivate
     * @return int 0 - err 1 - ok 2 - ok, уже есть
     */
    public function registerUser($email, $reg, $fio = '', $phone = '', $sendActivate = true)
    {
        $ret = 0;
        $User = User::findOne(['Email' => $email, 'IMEI' => '0', 'IsDeleted' => 0]);

        if (!$User && $reg) {
            $User = User::findOne(['TempEmail' => $email, 'Email' => null, 'IMEI' => '0']);
            if (!$User) {
                //новый
                $User = new User();
                $User->Email = null;
                $User->TempEmail = $email;
                $User->VerificCode = '';
                $User->DateRegister = 0;
                $User->ExtCustomerIDP = md5($email);
                $User->IMEI = '0';
                $User->UserDeviceType = 1;
                if (!empty($fio)) {
                    $fio = explode(' ', $fio);
                    if (isset($fio[0])) $User->Fam = $fio[0];
                    if (isset($fio[1])) $User->Name = $fio[1];
                    if (isset($fio[2])) $User->Otch = $fio[2];
                }
                if (!empty($phone)) {
                    $User->Phone = $phone;
                }
            } else {
                //уже есть недоактивированный
                $User->IsDeleted = 0;
                $User->DateRegister = 0;
            }

            $User->save(false);
            if ($sendActivate) {
                $ret = $this->sendActivation($User);
            } else {
                $ret = 1;
            }

        } elseif ($User) {
            $this->User = $User;
            $ret = 2;
        }

        return intval($ret);
    }

    /**
     * @param $email
     * @return User
     */
    public function findUser($email)
    {
        $this->User = User::findOne(['Email' => $email, 'IMEI' => '0', 'IsDeleted' => 0]);
        return $this->User;
    }

    /**
     * Отправка кода активации
     * @param User $User
     * @return bool
     */
    private function sendActivation($User)
    {
        if ($User && !empty($User->TempEmail)) {

            $ActivationCode = abs(crc32($User->ID . $User->TempEmail . date('dmy')));
            $ActivationCode = substr($ActivationCode, 0, 6);

            $User->VerificCode = $ActivationCode;
            $User->save(false);

            $subject = 'VePay - подтверждение регистрации';
            $content = \Yii::$app->view->renderFile("@app/mail/activate_template.php", [
                "ActivationCode" => $ActivationCode
            ]);

            $SendMail = new SendEmail();
            $SendMail->send($User->TempEmail, '', $subject, $content);

            return true;
        }
        return false;
    }

    /**
     * Активация пользователя
     * @param $email
     * @param $code
     * @return bool
     */
    public function activateUser($email, $code)
    {
        $User = User::findOne(['TempEmail' => $email, 'IMEI' => '0', 'IsDeleted' => 0]);
        if ($User) {
            if ($User->VerificCode == $code) {
                $User->Email = $User->TempEmail;
                $User->TempEmail = null;
                $User->VerificCode = null;
                $User->DateRegister = time();
                $User->save(false);
                $this->User = $User;
                return true;
            }
        }
        return false;
    }

    /**
     * Активация пользователя без кода
     * @param $email
     * @return bool
     */
    public function activateUserAuto($email)
    {
        $User = User::findOne(['TempEmail' => $email, 'IMEI' => '0', 'IsDeleted' => 0]);
        if ($User) {
            $User->Email = $User->TempEmail;
            $User->TempEmail = null;
            $User->VerificCode = null;
            $User->DateRegister = time();
            $User->save(false);
            $this->User = $User;
            return true;
        }
        return false;
    }

    /**
     * @param User $User
     * @param string $fio
     */
    public function updateFio($User, $fio)
    {
        if (!empty($fio) && $User) {
            $fio = explode(' ', $fio);
            if (isset($fio[0])) $User->Fam = $fio[0];
            if (isset($fio[1])) $User->Name = $fio[1];
            if (isset($fio[2])) $User->Otch = $fio[2];
            $User->save(false);
            $this->User = $User;
        }
    }

    /**
     * @param User $User
     * @param string $phone
     */
    public function updatePhone($User, $phone)
    {
        if (!empty($phone) && $User) {
            $User->Phone = $phone;
            $User->save(false);
            $this->User = $User;
        }
    }

    /**
     * Обновление подписки на рассыку ссылок на оплату
     * @param User $User
     * @param Provparams $ProvParams
     * @param boolean $isSendkvit
     */
    public function updatePodpiskaShablon($User, $ProvParams, $isSendkvit)
    {

    }
}