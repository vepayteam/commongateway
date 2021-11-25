<?php


namespace app\services\balance\traits;


use app\models\PayschetPart;
use app\services\balance\models\PartsBalanceForm;
use app\services\balance\models\PartsBalancePartnerForm;

/**
 * @deprecated
 */
trait PartsTrait
{
    /**
     * @param PartsBalanceForm $partsBalanceForm
     * @return array
     */
    public function getPartsBalance(PartsBalanceForm $partsBalanceForm)
    {
        $result = [
            'draw' => $partsBalanceForm->draw,
        ];
        $query = PayschetPart::find()
            ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
            ->innerJoin('partner', 'partner.ID = pay_schet_parts.PartnerId')
            ->leftJoin('vyvod_parts', 'vyvod_parts.ID = pay_schet_parts.VyvodId AND vyvod_parts.Status = 1')
            ->where([
                'pay_schet.IdOrg' => $partsBalanceForm->getPartner()->ID,
                'pay_schet.Status' => '1',
            ])
            ->andWhere(['>=', 'pay_schet.DateCreate', strtotime($partsBalanceForm->filters['datefrom'].':00')])
            ->andWhere(['<=', 'pay_schet.DateCreate', strtotime($partsBalanceForm->filters['dateto'])]);

        $result['recordsTotal'] = $query->count();

        foreach ($partsBalanceForm->columns as $column) {
            if(!empty($column['search']['value'])) {
                $arr = explode(' AS ', $column['name']);
                $query->andWhere([
                    'like',
                    $arr[0],
                    $column['search']['value']
                ]);
            }
        }

        $result['recordsFiltered'] = $query->count();

        $query->limit($partsBalanceForm->length);
        $query->offset($partsBalanceForm->start);

        // подмена даты
        $columns = PartsBalanceForm::COLUMNS_BY_PARTS_BALANCE;
        unset($columns['DateCreate'], $columns['VyvodDateCreate']);
        $columns = array_keys($columns);
        $columns[] = 'FROM_UNIXTIME(pay_schet.DateCreate) AS DateCreate';
        $columns[] = 'FROM_UNIXTIME(vyvod_parts.DateCreate) AS VyvodDateCreate';

        $columnNOrder = $partsBalanceForm->order[0]['column'];
        $orderColumn = $partsBalanceForm->columns[$columnNOrder]['data'];
        $orderDir = $partsBalanceForm->order[0]['dir'];
        $query->orderBy($orderColumn.' '.$orderDir);

        $query->addSelect($columns);
        $result['data'] = $query->asArray()->all();
        return $result;
    }

    /**
     * @param PartsBalancePartnerForm $partsBalancePartnerForm
     * @return array
     */
    public function getPartsBalancePartner(PartsBalancePartnerForm $partsBalancePartnerForm)
    {
        $result = [
            'draw' => $partsBalancePartnerForm->draw,
        ];
        $query = PayschetPart::find()
            ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
            ->innerJoin('partner', 'partner.ID = pay_schet_parts.PartnerId')
            ->leftJoin('vyvod_parts', 'vyvod_parts.ID = pay_schet_parts.VyvodId AND vyvod_parts.Status = 1')
            ->where([
                'pay_schet_parts.PartnerId' => $partsBalancePartnerForm->getPartner()->ID,
                'pay_schet.Status' => '1',
            ])
            ->andWhere(['>=', 'pay_schet.DateCreate', strtotime($partsBalancePartnerForm->filters['datefrom'].':00')])
            ->andWhere(['<=', 'pay_schet.DateCreate', strtotime($partsBalancePartnerForm->filters['dateto'])]);

        $result['recordsTotal'] = $query->count();

        foreach ($partsBalancePartnerForm->columns as $column) {
            if(!empty($column['search']['value'])) {
                $arr = explode(' AS ', $column['name']);
                $query->andWhere([
                    'like',
                    $arr[0],
                    $column['search']['value']
                ]);
            }
        }

        $result['recordsFiltered'] = $query->count();

        $query->limit($partsBalancePartnerForm->length);
        $query->offset($partsBalancePartnerForm->start);

        // подмена даты
        $columns = PartsBalancePartnerForm::COLUMNS_BY_PARTS_BALANCE;
        unset($columns['DateCreate'], $columns['VyvodDateCreate']);
        $columns = array_keys($columns);
        $columns[] = 'FROM_UNIXTIME(pay_schet.DateCreate) AS DateCreate';
        $columns[] = 'FROM_UNIXTIME(vyvod_parts.DateCreate) AS VyvodDateCreate';

        $columnNOrder = $partsBalancePartnerForm->order[0]['column'];
        $orderColumn = $partsBalancePartnerForm->columns[$columnNOrder]['data'];
        $orderDir = $partsBalancePartnerForm->order[0]['dir'];
        $query->orderBy($orderColumn.' '.$orderDir);

        $query->addSelect($columns);
        $result['data'] = $query->asArray()->all();
        return $result;
    }
}
