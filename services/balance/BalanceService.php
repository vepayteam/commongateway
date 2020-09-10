<?php


namespace app\services\balance;


use app\models\PayschetPart;
use app\services\balance\models\PartsBalanceForm;

class BalanceService
{

    public function getPartsBalance(PartsBalanceForm $partsBalanceForm)
    {
        $result = [];

        $q = PayschetPart::find()
            ->addSelect([
                'pay_schet.*',
                'pay_schet_parts.*',
            ])
            ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
            ->where([
                'pay_schet.IdOrg' => $partsBalanceForm->getPartner()->ID,
                'pay_schet.Status' => '1',
            ])
            ->andWhere(['>=', 'pay_schet.DateCreate', strtotime($partsBalanceForm->datefrom)])
            ->andWhere(['<=', 'pay_schet.DateCreate', strtotime($partsBalanceForm->dateto)])
            ->asArray()
            ->all();

        foreach ($q as $row) {
            if(!array_key_exists($row['PartnerId'], $result)) {
                $result[$row['PartnerId']] = [];
            }
            $result[$row['PartnerId']][] = $row;
        }
        return $result;
    }

}
