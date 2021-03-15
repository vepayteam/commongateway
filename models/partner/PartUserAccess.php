<?php

namespace app\models\partner;

use Yii;
use yii\base\Action;
use yii\web\User;

/**
 * This is the model class for table "part_user_access".
 *
 * @property string $ID
 * @property string $IdUser
 * @property string $IdRazdel
 */
class PartUserAccess extends \yii\db\ActiveRecord
{
    //настройки доступа
    public static $razdely = [
        0 => 'Операции',
        1 => 'Отчет',
        2 => 'Точки',
        3 => 'Компания',
        6 => 'Счета',
        52 => 'Настройки',
        7 => 'Колбэки'
    ];

    //разделы для мерчантов /partner
    public static $razdelAct = [
        'stat/list' => 0,
        'stat/otch' => 1,
        'stat/sale' => 10,
        'stat/saledraft' => 11,
        'stat/salekonvers' => 12,
        'stat/platelshik' => 13,
        'stat/recurrentpays' => 14,
        'stat/recurrentcomis' => 15,
        'stat/recurrentremove' => 16,
        'stat/recurrentmiddle' => 17,
        'stat/recurrentcard' => 23,
        'stat/acts' => 18,
        'uslug/index' => 2,
        'uslug/point-edit' => 2,
        'uslug/point-add' => 2,
        'uslug/point-save' => 2,
        'partner/index' => 3,
        'partner/partner-edit' => 3,
        'partner/partner-add' => 3,
        'partner/partner-save' => 3,
        'partner/dogovor-edit' => 3,
        'partner/users-edit' => 3,
        'partner/users-add' => 3,
        'order/index' => 6,
        'order/add' => 6,
        'callback/list' => 7,
        'settings/index' => 52
    ];

    //все разделы
    private static $razdelActFull = [
        '' => -1,
        'stat/list' => 0,
        'stat/otch' => 1,
        'stat/sale' => 10,
        'stat/saledraft' => 11,
        'stat/salekonvers' => 12,
        'stat/platelshik' => 13,
        'stat/recurrentpays' => 14,
        'stat/recurrentcomis' => 15,
        'stat/recurrentremove' => 16,
        'stat/recurrentmiddle' => 17,
        'stat/recurrentcard' => 23,
        'stat/diff' => 24,
        'uslug/index' => 2,
        'uslug/point-edit' => 2,
        'uslug/point-add' => 2,
        'uslug/point-save' => 2,
        'partner/index' => 3,
        'partner/partner-edit' => 3,
        'partner/partner-add' => 3,
        'partner/partner-save' => 3,
        'partner/dogovor-edit' => 3,
        'partner/users-edit' => 3,
        'order/index' => 6,
        'order/add' => 6,
        'callback/list' => 7,
        'admin/comisotchet' => 8,
        'admin/bank' => 8,
        'mfo/index' => 50,
        'mfo/balance' => 51,
        'cardkey/index' => 90,
        'cardkey/initkeys' => 90,
        'cardkey/changekeys' => 90,
        'cardkey/testkek' => 90,
        'cardkey/insertkek3' => 90,
        'cardkey/insertkek2' => 90,
        'cardkey/insertkek1' => 90,
        'default/chngpassw' => 100,
        'payment-orders/list' => 18,
        'antifraud/index' => 19,
        'antifraud/all-stat' => 20,
        'stat/acts' => 21,
        'stat/act-edit' => 21,
        'antifraud/settings' => 22,
        'settings/index' => 52,
        'settings/distribution' => 53,
        'settings/alarms' => 54
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'part_user_access';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdUser', 'IdRazdel'], 'required'],
            [['IdUser', 'IdRazdel'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdUser' => 'Id User',
            'IdRazdel' => 'Id Razdel',
        ];
    }

    /**
     * @param PartnerUsers $user
     * @param array $razdely
     */
    public static function saveRazdels($user, $razdely)
    {
        $accessList = self::findAll(['IdUser' => $user->ID]);
        $exist = [];
        foreach ($accessList as $akLst) {
            $exist[] = $akLst->IdRazdel;
            if (!in_array($akLst->IdRazdel, $razdely)) {
                $akLst->delete();
            }
        }

        foreach ($razdely as $akLst) {
            if (!in_array($akLst, $exist)) {
                $ak = new self();
                $ak->IdUser = $user->ID;
                $ak->IdRazdel = $akLst;
                $ak->save();
            }
        }
    }

    /**
     * Доступ к разделу
     * @param User $user
     * @param Action $action
     * @return bool
     * @throws \Throwable
     */
    public static function checkRazdelAccess(User $user, Action $action)
    {
        $act = $action->controller->id . '/' . $action->id;
        $rz = self::$razdelAct[$act] ?? -1;
        if ($rz >= 0) {
            $accessList = $user->getIdentity() ? self::findAll(['IdUser' => $user->getIdentity()->getIdUser()]) : [];
            if (count($accessList) > 0) {
                foreach ($accessList as $ulst) {
                    if ($rz == $ulst->IdRazdel) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /**
     * @param Action $action
     * @return int|mixed
     */
    public static function getRazdelId($action)
    {
        $act = $action->controller->id . '/' . $action->id;
        return self::$razdelActFull[$act] ?? -1;
    }

    /**
     * Выделить выбранный раздел меню
     * @param Action $action
     * @return array
     */
    public static function getSelRazdel($action)
    {
        $razd = [];
        $act = self::getRazdelId($action);
        foreach (self::$razdelActFull as $a => $r) {
            if ($r == $act) {
                $razd[$r] = 'active';
            } else {
                $razd[$r] = '';
            }
        }

        return $razd;
    }
}
