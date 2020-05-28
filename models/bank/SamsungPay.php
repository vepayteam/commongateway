<?php

namespace app\models\bank;

use Yii;

class SamsungPay
{
    public function GetConf($IdPartner)
    {
        $res = Yii::$app->db->createCommand('
            SELECT 
                `IsUseApplepay`
            FROM 
                `partner` 
            WHERE 
                `IsDeleted` = 0 AND `IsBlocked` = 0 AND `ID` = :IDMFO 
            LIMIT 1
        ', [':IDMFO' => $IdPartner]
        )->queryOne();
        return $res && $res['IsUseApplepay'] ? ['IsUseSamsungpay' => 1, 'Samsung_MerchantID' => '000000000000000000'] : ['IsUseSamsungpay' => 0];
    }
}