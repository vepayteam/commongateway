<?php

namespace app\models\bank;

class BankMerchant
{
    /**
     * Банк
     * @param $bank
     * @param $type
     * @return TCBank
     */
    public static function Get($bank)
    {
        if ($bank == TCBank::$bank) {
            return new TCBank();
        }
        return new TCBank();
    }

}