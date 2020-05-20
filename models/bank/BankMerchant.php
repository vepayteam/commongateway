<?php

namespace app\models\bank;

use app\models\payonline\Cards;
use app\models\TU;

class BankMerchant
{
    /**
     * Банк
     * @param $bank
     * @param IBankGate|null $gate
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
     * Выбор банка для платежа картой
     * @param $IdPartner
     * @param $typeUsl
     * @return IBank
     * @throws \yii\db\Exception
     */
    public static function GetWorkBank($IdPartner, $typeUsl)
    {
        $BankCheck = new BankCheck();
        $order = 0;
        $bankRet = null;
        do {
            $bank = $BankCheck->CheckWorkedIn($order);
            $gate = null;
            if ($bank) {
                $gate = self::Gate($IdPartner, $bank, $typeUsl);
            }
            if ($bank && $gate && $gate->IsGate()) {
                $bankRet = self::Get($bank, $gate);
            }
            if ($bankRet !== null) {
                $BankCheck->UpdatePay($bank);
            } else {
                $order++;
            }

        } while ($bankRet === null && $order < 2);
        return $bankRet;
    }

    /**
     * Выбор банка для платежа ApplePay
     * @return int
     */
    public static function GetApplePayBank()
    {
        $BankCheck = new BankCheck();
        $bank = $BankCheck->CheckWorkedApplePay();
        if ($bank) {
            $BankCheck->UpdatePay($bank);
        }
        return $bank;
    }

    /**
     * Выбор банка для выплаты
     * @return int
     */
    public static function GetWorkBankOut()
    {
        return TCBank::$bank;
    }
}