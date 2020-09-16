<?php


namespace app\services\balance;


use app\models\PayschetPart;
use app\services\balance\models\PartsBalanceForm;
use app\services\balance\models\PartsBalancePartnerForm;

class BalanceService
{


    public function getPartsBalance(PartsBalanceForm $partsBalanceForm)
    {
        $result = [
            'draw' => $partsBalanceForm->draw,
        ];
        $q = PayschetPart::find()
            ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
            ->innerJoin('partner', 'partner.ID = pay_schet_parts.PartnerId')
            ->where([
                'pay_schet.IdOrg' => $partsBalanceForm->getPartner()->ID,
                'pay_schet.Status' => '1',
            ])
            ->andWhere(['>=', 'pay_schet.DateCreate', strtotime($partsBalanceForm->filters['datefrom'].':00')])
            ->andWhere(['<=', 'pay_schet.DateCreate', strtotime($partsBalanceForm->filters['dateto'])]);

        $result['recordsTotal'] = $q->count();

        foreach ($partsBalanceForm->columns as $column) {
            if(!empty($column['search']['value'])) {
                $q->andWhere([
                    'like',
                    $column['name'],
                    $column['search']['value']
                ]);
            }
        }

        $result['recordsFiltered'] = $q->count();

        $q->limit($partsBalanceForm->length);
        $q->offset($partsBalanceForm->start);

        // подмена даты
        $columns = PartsBalanceForm::COLUMNS_BY_PARTS_BALANCE;
        unset($columns['DateCreate']);
        $columns = array_keys($columns);
        $columns[] = 'FROM_UNIXTIME(pay_schet.DateCreate) AS DateCreate';

        $columnNOrder = $partsBalanceForm->order[0]['column'];
        $orderColumn = $partsBalanceForm->columns[$columnNOrder]['data'];
        $orderDir = $partsBalanceForm->order[0]['dir'];
        $q->orderBy($orderColumn.' '.$orderDir);

        $q->addSelect($columns);
        $result['data'] = $q->asArray()->all();
        return $result;
    }

    public function getPartsBalancePartner(PartsBalancePartnerForm $partsBalancePartnerForm)
    {
        $result = [
            'draw' => $partsBalancePartnerForm->draw,
        ];
        $q = PayschetPart::find()
            ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
            ->innerJoin('partner', 'partner.ID = pay_schet_parts.PartnerId')
            ->where([
                'pay_schet_parts.PartnerId' => $partsBalancePartnerForm->getPartner()->ID,
                'pay_schet.Status' => '1',
            ])
            ->andWhere(['>=', 'pay_schet.DateCreate', strtotime($partsBalancePartnerForm->filters['datefrom'].':00')])
            ->andWhere(['<=', 'pay_schet.DateCreate', strtotime($partsBalancePartnerForm->filters['dateto'])]);

        $result['recordsTotal'] = $q->count();

        foreach ($partsBalancePartnerForm->columns as $column) {
            if(!empty($column['search']['value'])) {
                $q->andWhere([
                    'like',
                    $column['name'],
                    $column['search']['value']
                ]);
            }
        }

        $result['recordsFiltered'] = $q->count();

        $q->limit($partsBalancePartnerForm->length);
        $q->offset($partsBalancePartnerForm->start);

        // подмена даты
        $columns = PartsBalancePartnerForm::COLUMNS_BY_PARTS_BALANCE;
        unset($columns['DateCreate']);
        $columns = array_keys($columns);
        $columns[] = 'FROM_UNIXTIME(pay_schet.DateCreate) AS DateCreate';

        $columnNOrder = $partsBalancePartnerForm->order[0]['column'];
        $orderColumn = $partsBalancePartnerForm->columns[$columnNOrder]['data'];
        $orderDir = $partsBalancePartnerForm->order[0]['dir'];
        $q->orderBy($orderColumn.' '.$orderDir);

        $q->addSelect($columns);
        $result['data'] = $q->asArray()->all();
        return $result;
    }

}
