<?php

namespace app\models\bank;

use app\models\payonline\Cards;
use app\models\TU;

class BankMerchant
{
    /**
     * Банк
     * @param $bank
     * @return IBank
     * @throws \yii\db\Exception
     */
    public static function Get($bank, $gate = null)
    {
        if ($bank == TCBank::$bank) {
            return new TCBank($gate);
        } elseif ($bank == MTSBank::$bank) {
            return new MTSBank($gate);
        }
        return new TCBank($gate);
    }

    /**
     * @param array $params [Bank, IdOrg, IDPartner, IsCustom, IdUsluga]
     * @return IBank
     * @throws \yii\db\Exception
     */
    public static function Create($params)
    {
        if ($params['Bank'] == TCBank::$bank) {
            if ($params['IdUsluga'] == 1) {
                //регистрация карты
                $TcbGate = new TcbGate($params['IdOrg'], TCBank::$AUTOPAYGATE);
            } else {
                $TcbGate = new TcbGate($params['IDPartner'], null, $params['IsCustom']);
            }
            return new TCBank($TcbGate);
        } elseif ($params['Bank'] == MTSBank::$bank) {
            $mtsGate = new MtsGate($params['IDPartner'], null, $params['IsCustom']);
            return new MTSBank($mtsGate);
        }
        return new TCBank();
    }

    /**
     * @param $partner
     * @param $bank
     * @param $typeUsl
     * @return IBankGate|null
     */
    public static function Gate($partner, $bank, $typeUsl)
    {
        $Gate = null;
        if ($bank == TCBank::$bank) {
            $Gate = new TcbGate($partner, null, $typeUsl);
        } elseif ($bank == MTSBank::$bank) {
            $Gate = new MtsGate($partner, null, $typeUsl);
        }

        return $Gate;
    }

    /**
     * Выбор банка для платежа
     * @return int
     */
    public static function GetWorkBank()
    {
        $BankCheck = new BankCheck();
        if (!$BankCheck->CheckWorked(TCBank::$bank)) {
            $BankCheck->UpdatePay(MTSBank::$bank);
            return MTSBank::$bank;
        }
        $BankCheck->UpdatePay(TCBank::$bank);
        return TCBank::$bank;
    }
}