<?php

namespace app\models\bank;

use Yii;

class GooglePay
{
    public function GetConf($IdPartner)
    {
        $res = Yii::$app->db->createCommand('
            SELECT 
                `GoogleMerchantID` AS `Google_MerchantID`,
                `IsUseGooglepay`
            FROM 
                `partner` 
            WHERE 
                `IsDeleted` = 0 AND `IsBlocked` = 0 AND `ID` = :IDMFO 
            LIMIT 1
        ', [':IDMFO' => $IdPartner]
        )->queryOne();
        return $res;
    }
}