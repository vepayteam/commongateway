<?php

namespace app\models\bank;

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
}