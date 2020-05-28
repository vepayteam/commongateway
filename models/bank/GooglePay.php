<?php

namespace app\models\bank;

use Yii;

class GooglePay
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
        return $res && $res['IsUseApplepay'] ? ['IsUseGooglepay' => 1, 'Google_MerchantID' => '000000000000000000000'] : ['IsUseGooglepay' => 0];
    }
}