<?php

namespace app\models\payonline;

use app\models\api\Reguser;
//use app\models\protocol\OnlineProv;
use app\models\geolocation\GeoInfo;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfOut;
use app\models\kfapi\KfPay;
use GeoIp2\Record\MaxMind;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;

class CreatePay
{
    /* @var null|Provparams */
    protected $Provparams = null;
    /* @var null|array */
    protected $error = null;
    /* @var null|User */
    protected $user = null;

    private $smsNeed;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    /**
     * Создание платежа по Provparams
     * @param Provparams $Provparams
     * @param int $agent
     * @param int $TypeWidget
     * @param int $Bank
     * @param int $IdOrg
     * @param string $Extid
     * @param int $AutoPayIdGate
     * @return array|null
     * @throws \yii\db\Exception
     */
    public function createPay(Provparams $Provparams, $agent = 0, $TypeWidget = 0, $Bank = 0, $IdOrg = 0, $Extid = '', $AutoPayIdGate = 0)
    {
        $ret = null;

        $this->Provparams = $Provparams;

        $idpay = $this->addPayschet($agent,0, $TypeWidget, $Bank, $IdOrg, $Extid, $AutoPayIdGate);
        if ($idpay) {
            $ret = ['IdPay' => $idpay];
        }

        return $ret;
    }

    /**
     * Создание платежа
     * @param int $idCardActivate
     * @param KfCard $kfCard
     * @param int $TypwWidget
     * @param int $bank
     * @param int $org
     * @return array|null [IdPay, Sum]
     * @throws \yii\db\Exception
     */
    public function payActivateCard($idCardActivate, $kfCard, $TypwWidget, $bank = 0, $org = 0)
    {
        $ret = null;

        try {
            $sum = random_int(100, 1000) / 100.0;
        } catch (\Exception $e) {
            $sum = 100;
        }

        $this->loadData([
            'Provparams' => [
                'prov' => 1,
                'param' => [0 => $sum],
                'summ' => $sum
            ]
        ]);

        if ($this->Provparams->Usluga) {
            $idpay = $this->addPayschet(0, $idCardActivate, $TypwWidget, $bank, $org, $kfCard->extid,0,$kfCard->timeout * 60);
            if (!empty($kfCard->successurl)) {
                $this->setReturnUrl($idpay, $kfCard->successurl);
            }
            if (!empty($kfCard->failurl)) {
                $this->setReturnUrlFail($idpay, $kfCard->failurl);
            }
            if (!empty($kfCard->cancelurl)) {
                $this->setReturnUrlFail($idpay, $kfCard->cancelurl);
            }
            if (!empty($kfCard->postbackurl)) {
                $this->setReturnUrlPostback($idpay, $kfCard->postbackurl);
            }
            if (!empty($kfCard->postbackurl_v2)) {
                $this->setReturnUrlPostbackV2($idpay, $kfCard->postbackurl_v2);
            }

            $ret = ['IdPay' => $idpay, 'Sum' => $sum];
        }
        return $ret;
    }

    /**
     * Занесение данных
     * @param array $formData
     */
    protected function loadData($formData)
    {
        $this->Provparams = new Provparams();
        $this->Provparams->load($formData);
        $this->Provparams->summ = round(floatval($this->Provparams->summ) * 100.0);
        $this->Provparams->Usluga = Uslugatovar::findOne(['ID' => $this->Provparams->prov]);
    }

    /**
     * Данные платежа
     * @param int $idpay
     * @param int $IdUser
     * @return Provparams|null
     * @throws \yii\db\Exception
     */
    public function getInfoPay($idpay, &$IdUser)
    {
        if ($idpay) {

            $paySchet = Yii::$app->db->createCommand('
                SELECT
                    `ID`,
                    `QrParams`,
                    `IdQrProv`,
                    `SummPay`,
                    `IdUser`
                FROM
                    `pay_schet`
                WHERE
                    `ID` = :IDPS
            ', [
                ':IDPS' => $idpay
            ])->queryOne();

            if ($paySchet) {
                $this->Provparams = new Provparams();
                $this->Provparams->param = explode('|', $paySchet['QrParams']);
                $this->Provparams->prov = $paySchet['IdQrProv'];
                $this->Provparams->summ = $paySchet['SummPay'];
                $this->Provparams->Usluga = Uslugatovar::findOne(['ID' => $paySchet['ID']]);

                $IdUser = $paySchet['IdUser'];
            }
        }

        return $this->Provparams;
    }

    /**
     * Проверка введенных данных
     * @return bool
     */
    protected function checkParams()
    {
        $ret = $this->checkSumm();
        if ($ret) {
            $ret = $this->chekParamsByMask();
        }
        return $ret;
    }

    /**
     * Проверка сумма платежа
     * @return bool
     */
    protected function checkSumm()
    {
        $ret = true;
        if (!$this->Provparams->validateSumm()) {
            $this->error['mesg'] = 'Неверная сумма';
            $ret = false;
        }
        return $ret;
    }

    /**
     * Проверка номера по маске
     * @return bool
     */
    protected function chekParamsByMask()
    {
        $ret = true;
        $errval = '';
        if (!$this->Provparams->validateByRegex($errval)) {
            $this->error['mesg'] = 'Неверно указан параметр: ' . $errval;
            $ret = false;
        }
        return $ret;
    }

    /**
     * Сохранение для платежа данных
     * @param int $agent
     * @param int $idCardActivate
     * @param int $TypeWidget [0 - web/communal 1 - mobile 2 - merchant 3 - mfo]
     * @param int $Bank
     * @param int $IdOrg
     * @param string $Extid
     * @param int $AutoPayIdGate
     * @param int $timeout
     * @param string $dogovor
     * @param string $fio
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function addPayschet($agent = 0, $idCardActivate = 0, $TypeWidget = 0, $Bank = 0, $IdOrg = 0, $Extid = '', $AutoPayIdGate = 0, $timeout = 86400, $dogovor = '', $fio = '')
    {
        // !!! изменен default переменной $timeout, старое значение 900

        // добавляем в таблицу pay_schet
        Yii::$app->db->createCommand()
            ->insert('pay_schet', [
                'IdUsluga' => $this->Provparams->Usluga->ID,
                'IdQrProv' => intval($this->Provparams->Usluga->ProfitIdProvider),
                'QrParams' => implode("|", $this->Provparams->param),
                'IdUser' => $this->user ? $this->user->ID : 0,
                'SummPay' => $this->Provparams->summ,
                'UserClickPay' => 0,
                'DateCreate' => time(),
                'IdKard' => $idCardActivate,
                'Status' => 0,
                'DateOplat' => 0,
                'DateLastUpdate' => time(),
                'PayType' => 0,
                'TimeElapsed' => $timeout,
                'ExtKeyAcces' => 0,
                'CountSendOK' => 0,
                'Period' => 0,
                'ComissSumm' => $this->Provparams->calcComiss(),
                'MerchVozn' => $this->Provparams->calcMerchVozn(),
                'BankComis' => $this->Provparams->calcBankComis(),
                'Schetcheks' => '',
                'IdAgent' => $agent,
                'IsAutoPay' => $AutoPayIdGate > 0 ? 1 : 0,
                'AutoPayIdGate' => $AutoPayIdGate,
                'TypeWidget' => $TypeWidget,
                'Bank' => $Bank,
                'IdOrg' => $IdOrg,
                'Extid' => $Extid,
                'sms_accept'=> ($this->smsNeed === 1 ? 0 : 1),
                'Dogovor' => $dogovor,
                'FIO' => $fio,
            ])
            ->execute();
        Yii::warning("addPayschet IdKard=$idCardActivate Bank=$Bank, IdOrg=$IdOrg Extid=$Extid", 'payschet');
        $IdPay = Yii::$app->db->getLastInsertID();
        return $IdPay;
    }

    /**
     * Обновление данных для платежа
     * @param int $idpay
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function updatePayschet($idpay)
    {
        // добавляем в таблицу pay_schet
        Yii::$app->db->createCommand()
            ->update('pay_schet', [
                'IdUsluga' => $this->Provparams->Usluga->ID,
                'IdQrProv' => $this->Provparams->Usluga->ProfitIdProvider,
                'QrParams' => implode("|", $this->Provparams->param),
                'IdUser' => $this->user ? $this->user->ID : 0,
                'SummPay' => $this->Provparams->summ,
                'DateLastUpdate' => time(),
                'ComissSumm' => $this->Provparams->calcComiss(),
            ], 'ID = :IDPS AND Status = 0', [':IDPS' => $idpay])
            ->execute();

        $IdPay = Yii::$app->db->getLastInsertID();
        return $IdPay;
    }

    /**
     * Ошибка
     * @return string
     */
    public function getError()
    {
        return $this->error['mesg'];
    }

    /**
     * @return null|Provparams
     */
    public function getProvparams()
    {
        return $this->Provparams;
    }

    /**
     * Обновление IdUser в счете
     * @param User $user
     * @param int $IdPay
     * @param int $IsSendKvit
     * @throws \yii\db\Exception
     */
    public function setUserToPaySchet($user, $IdPay, $IsSendKvit)
    {
        if ($user && $IdPay) {
            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'IdUser' => $user->ID,
                    'SendKvitMail' => $IsSendKvit
                ],
                    'ID = :ID', [
                        ':ID' => $IdPay
                    ])
                ->execute();
        }
    }

    /**
     * Обновление карты в счете
     * @param int $IdPay
     * @param int $IdKard
     * @throws \yii\db\Exception
     */
    public function setKardToPaySchet($IdPay, $IdKard)
    {
        if ($IdKard) {
            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'IdKard' => $IdKard
                ],
                    'ID = :ID', [
                        ':ID' => $IdPay
                    ])
                ->execute();
        }
    }

    /**
     * Вычада займа на карту
     *
     * @param null|User $user
     * @param array $params
     * @param KfOut $kfOut
     * @param float $amount
     * @param int $usl
     * @param int $bank
     * @param int $IdOrg
     * @param int $needSms
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function payToCard($user, $params, KfOut $kfOut, $usl, $bank, $IdOrg, $needSms = 0)
    {
        $this->smsNeed = $needSms;
        $this->Provparams = new Provparams();
        $this->Provparams->prov = $usl;
        $this->Provparams->param = $params;
        $this->Provparams->summ = round(floatval($kfOut->amount) * 100.0);
        $this->Provparams->Usluga = Uslugatovar::findOne(['ID' => $this->Provparams->prov]);

        $this->user = $user;

        $IdPay = $this->addPayschet(0,0,3, $bank, $IdOrg, $kfOut->extid,0,900, $kfOut->document_id, $kfOut->fullname);

        return ['IdPay' => $IdPay, 'summ' => $this->Provparams->summ + $this->Provparams->calcComiss()];
    }

    /**
     * Погашение займа
     * @param $user
     * @param array $params
     * @param KfPay $KfPay
     * @param float $amount
     * @param $usl
     * @param $bank
     * @param $IdOrg
     * @param $AutoPayIdGate
     * @return array
     * @throws \yii\db\Exception
     */
    public function payToMfo($user, $params, KfPay $KfPay, $usl, $bank, $IdOrg, $AutoPayIdGate)
    {
        $this->Provparams = new Provparams();
        $this->Provparams->prov = $usl;
        $this->Provparams->param = $params;
        $this->Provparams->summ = round((float)($KfPay->amount) * 100.0);
        $this->Provparams->Usluga = Uslugatovar::findOne(['ID' => $this->Provparams->prov]);

        $this->user = $user;

        $IdPay = $this->addPayschet(0, 0, 3, $bank, $IdOrg, $KfPay->extid, $AutoPayIdGate,$KfPay->timeout * 60, $KfPay->document_id, $KfPay->fullname);
        if (!empty($KfPay->successurl)) {
            $this->setReturnUrl($IdPay, $KfPay->successurl);
        } elseif (!empty($this->Provparams->Usluga->UrlReturn)) {
            $this->setReturnUrl($IdPay, $this->Provparams->Usluga->UrlReturn);
        }

        if (!empty($KfPay->failurl)) {
            $this->setReturnUrlFail($IdPay, $KfPay->failurl);
        } elseif (!empty($this->Provparams->Usluga->UrlReturnFail)) {
            $this->setReturnUrlFail($IdPay, $this->Provparams->Usluga->UrlReturnFail);
        }

        if (!empty($KfPay->cancelurl)) {
            $this->setReturnUrlCancel($IdPay, $KfPay->cancelurl);
        } elseif (!empty($this->Provparams->Usluga->UrlReturnCancel)) {
            $this->setReturnUrlCancel($IdPay, $this->Provparams->Usluga->UrlReturnCancel);
        }

        if (!empty($KfPay->postbackurl)) {
            $this->setReturnUrlPostback($IdPay, $KfPay->postbackurl);
        }

        if (!empty($KfPay->postbackurl_v2)) {
            $this->setReturnUrlPostbackV2($IdPay, $KfPay->postbackurl_v2);
        }

        return ['IdPay' => $IdPay, 'summ' => $this->Provparams->summ + $this->Provparams->calcComiss(), 'TimeElapsed' => round($KfPay->timeout), 'checkurl' => $this->Provparams->Usluga->UrlCheckReq];
    }

    /**
     * Найти пользователя и занести в счет
     * @param array|null $formUsr
     * @param array|null $form
     * @param array $data
     * @throws \yii\db\Exception
     */
    public function setUserInfo($formUsr, $form, &$data)
    {
        if (isset($formUsr['email'])) {
            //web пользователь
            $data['email'] = $formUsr['email'];

            $userInfo = new Userinfo();
            $u = $userInfo->findUser($formUsr['email']);
            if (!$u) {
                if ($userInfo->registerUser($formUsr['email'], true,'','',false) == 1) {
                    $userInfo->activateUserAuto($formUsr['email']);
                }
                $u = $userInfo->findUser($formUsr['email']);
            }
            if ($u) {
                $this->setUserToPaySchet($u, $data['IdPay'], 1);
                $userInfo->updatePodpiskaShablon($u, $this->getProvparams(),
                    isset($formUsr['sendkvit']));
            }
        } elseif (isset($form['imei'])) {
            //мобильный пользователь
            $reguer = new Reguser();
            $u = $reguer->findUser($form['imei'], $form['extuser'], $form['extpw'], $form['extorg']);
            if ($u) {
                $data['user'] = $u;
                if (!empty($u->Email)) {
                    $data['email'] = $u->Email;
                }
                $card = $reguer->getCard($u->ID);
                if ($card) {
                    $data['IdKard'] = $card->ID;
                    $this->setKardToPaySchet($data['IdPay'], $data['IdKard']);
                }
                $this->setUserToPaySchet($u, $data['IdPay'], 0);
            }
        }
    }

    /**
     * Групповая оплата счета
     * @param array $data [IdPay]
     * @param Provparams $provparams
     * @param $paygroup
     * @return int
     * @throws \yii\db\Exception
     */
    public function SetCartPay(array $data, Provparams $provparams, &$paygroup)
    {
        if ($paygroup == 0) {
            //новая группа
            Yii::$app->db->createCommand()->insert('pay_schgroup', [
                'DateAdd' => time(),
                'DateOplat' => 0,
                'Status' => 0
            ])->execute();
            $paygroup = Yii::$app->db->getLastInsertID();
        }

        $grp = Yii::$app->db->createCommand('
            SELECT
                `ID`,
                `CountPays`,
                `SummPays`,
                `ComisPays`
            FROM
                `pay_schgroup`
            WHERE
                `ID` = :ID
                AND `status` = 0
        ', [':ID' => $paygroup])->queryOne();

        if ($grp) {

           $tr = Yii::$app->db->beginTransaction();

            Yii::$app->db->createCommand()->update('pay_schet', [
                'IdGroupOplat' => $paygroup
            ], ['ID' => $data['IdPay']])->execute();

            Yii::$app->db->createCommand()->update('pay_schgroup', [
                'CountPays' => $grp['CountPays'] + 1,
                'SummPays' => $grp['SummPays'] + $provparams->summ,
                'ComisPays' => $grp['ComisPays'] + $provparams->calcComiss()
            ], ['ID' => $grp['ID']])->execute();

            $tr->commit();

            return 1;
        }
        return 0;
    }

    public function SetCallback(array $data, $callback, $callbackKey)
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'UserUrlInform' => $callback,
            'UserKeyInform' => $callbackKey
        ], ['ID' => $data['IdPay']])->execute();
    }

    public function SetForwardUrl(array $data, $successUrl, $failedUrl)
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'SuccessUrl' => $successUrl,
            'FailedUrl' => $failedUrl
        ], ['ID' => $data['IdPay']])->execute();
    }

    /**
     * Список в группе для оплаты
     * @param $paygroup
     * @return array
     * @throws \yii\db\Exception
     */
    public function GetCartList($paygroup)
    {
        $res = Yii::$app->db->createCommand('
            SELECT
                ps.`ID`,
                us.`NameUsluga`,
                ps.SummPay,
                ps.ComissSumm,
                ps.QrParams
            FROM
                `pay_schet` AS ps
                LEFT JOIN `pay_schgroup` AS pg ON ps.IdGroupOplat = pg.ID
                LEFT JOIN `uslugatovar` AS us ON ps.IdUsluga = us.ID
            WHERE
                pg.`ID` = :ID
                AND pg.`status` = 0
        ', [':ID' => $paygroup])->query();

        $ret = [];
        while ($row = $res->read()) {
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * проверка на повтор счета по внешнему id
     * @param string $extid
     * @param $usl
     * @param $IdOrg
     * @return array|null
     * @throws \yii\db\Exception
     */
    public function getPaySchetExt($extid, $usl, $IdOrg)
    {
        $ret = null;
        $res = Yii::$app->db->createCommand('
            SELECT
                ps.`ID`,
                ps.SummPay,
                ps.ComissSumm,
                ps.Status,
                ps.DateCreate,
                ps.UrlFormPay,
                ps.QrParams
            FROM
                `pay_schet` AS ps
            WHERE
                ps.`Extid` = :EXTID
                AND ps.`IdUsluga` = :USL
                AND ps.`IdOrg` = :IDORG
                AND ps.DateCreate > UNIX_TIMESTAMP() - 86400 * 100
        ', [':EXTID' => $extid, ':USL' => $usl, ':IDORG' => $IdOrg]
        )->queryOne();

        if ($res) {
            $ret = [
                'IdPay' => $res['ID'],
                'sumin' => $res['SummPay'] / 100.0,
                'summ' => $res['SummPay'] + $res['ComissSumm'],
                'url' => $res['UrlFormPay'],
                'params' => $res['QrParams']
            ];
        }

        return $ret;
    }

    /**
     * Не прошел запрос в банк - отменить сразу счет
     * @param $IdPay
     * @param string $message
     * @throws \yii\db\Exception
     */
    public function CancelReq($IdPay, $message = 'Ошибка запроса')
    {
        $res = Yii::$app->db->createCommand('
            SELECT
                ps.`ID`,
                ps.SummPay,
                ps.ComissSumm,
                ps.Status
            FROM
                `pay_schet` AS ps
            WHERE
                ps.`ID` = :ID
                AND ps.`Status` = 0
        ', [':ID' => $IdPay])->queryOne();

        if ($res) {
            Yii::$app->db->createCommand()->update('pay_schet', [
                'Status' => 2,
                'ErrorInfo' => mb_substr($message, 0, 250)
            ], '`ID` = :ID', [':ID' => $IdPay])->execute();
        }
    }

    public function setIdOrder($IdOrder, $data)
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'IdOrder' => $IdOrder
        ], ['ID' => $data['IdPay']])->execute();

    }

    private function setReturnUrl($IdPay, $UrlReturn)
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'SuccessUrl' => $UrlReturn
        ], ['ID' => $IdPay])->execute();

    }

    private function setReturnUrlFail($IdPay, $UrlReturnFail)
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'FailedUrl' => $UrlReturnFail
        ], ['ID' => $IdPay])->execute();

    }

    private function setReturnUrlCancel($IdPay, $UrlReturnCancel)
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'CancelUrl' => $UrlReturnCancel
        ], ['ID' => $IdPay])->execute();

    }

    private function setReturnUrlPostback($IdPay, $UrlReturnPostback)
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'PostbackUrl' => $UrlReturnPostback
        ], ['ID' => $IdPay])->execute();

    }

    private function setReturnUrlPostbackV2($IdPay, $UrlReturnPostbackV2)
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'PostbackUrl_v2' => $UrlReturnPostbackV2
        ], ['ID' => $IdPay])->execute();

    }

}
