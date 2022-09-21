<?php


namespace app\services\balance\traits;


use app\models\PayschetPart;
use app\services\balance\models\PartsBalanceForm;
use app\services\balance\models\PartsBalancePartnerForm;
use yii\db\Query;

/**
 * @deprecated
 * @todo Remove unused class.
 */
trait PartsTrait
{
    /**
     * @param PartsBalanceForm $partsBalanceForm
     * @return array
     * @throws \Exception
     * @todo Refactor or remove: unsafe and fragile code.
     */
    public function getPartsBalance(PartsBalanceForm $partsBalanceForm)
    {
        $result = [
            'draw' => $partsBalanceForm->draw,
        ];

        $refundPaySchetSubQuery = new Query();
        $refundPaySchetSubQuery
            ->from('pay_schet as refund_pay_schet')
            ->andWhere('refund_pay_schet.RefundSourceId=pay_schet.ID');

        $q = PayschetPart::find()
            ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
            ->innerJoin('partner', 'partner.ID = pay_schet_parts.PartnerId')
            ->leftJoin('vyvod_parts', 'vyvod_parts.ID = pay_schet_parts.VyvodId AND vyvod_parts.Status = 1')
            ->where([
                'pay_schet.IdOrg' => $partsBalanceForm->getPartner()->ID,
                'pay_schet.Status' => '1',
            ])
            ->andWhere(['>=', 'pay_schet.DateCreate', strtotime($partsBalanceForm->filters['datefrom'].':00')])
            ->andWhere(['<=', 'pay_schet.DateCreate', strtotime($partsBalanceForm->filters['dateto'])])
            ->andWhere(['not exists', $refundPaySchetSubQuery]);

        $result['recordsTotal'] = $q->count();

        foreach ($partsBalanceForm->columns as $column) {
            if (!in_array($column['name'], array_keys(PartsBalanceForm::COLUMNS_BY_PARTS_BALANCE))) {
                throw new \Exception("Invalid column name: \"{$column['name']}\".");
            }
            if(!empty($column['search']['value'])) {
                $arr = explode(' AS ', $column['name']);
                $q->andWhere([
                    'like',
                    $arr[0],
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
        unset($columns['VyvodDateCreate']);
        $columns = array_keys($columns);
        $columns[] = 'FROM_UNIXTIME(pay_schet.DateCreate) AS DateCreate';
        $columns[] = 'FROM_UNIXTIME(vyvod_parts.DateCreate) AS VyvodDateCreate';

        $columnNOrder = $partsBalanceForm->order[0]['column'];
        $orderColumn = $partsBalanceForm->columns[$columnNOrder]['data'];
        if (!in_array($orderColumn, $this->getValidOrderColumns($columns))) {
            throw new \Exception("Invalid order column name: \"{$orderColumn}\".");
        }
        $orderDir = strtoupper(trim($partsBalanceForm->order[0]['dir']));
        if (!in_array($orderDir, ['ASC', 'DESC', ''])) {
            throw new \Exception("Invalid order direction: \"{$orderDir}\".");
        }
        $q->orderBy($orderColumn.' '.$orderDir);

        $q->addSelect($columns);
        $result['data'] = $q->asArray()->all();
        return $result;
    }

    /**
     * @param PartsBalancePartnerForm $partsBalancePartnerForm
     * @return array
     * @throws \Exception
     * @todo Refactor or remove: unsafe and fragile code.
     */
    public function getPartsBalancePartner(PartsBalancePartnerForm $partsBalancePartnerForm)
    {
        $result = [
            'draw' => $partsBalancePartnerForm->draw,
        ];

        $refundPaySchetSubQuery = new Query();
        $refundPaySchetSubQuery
            ->from('pay_schet as refund_pay_schet')
            ->andWhere('refund_pay_schet.RefundSourceId=pay_schet.ID');

        $q = PayschetPart::find()
            ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
            ->innerJoin('partner', 'partner.ID = pay_schet_parts.PartnerId')
            ->leftJoin('vyvod_parts', 'vyvod_parts.ID = pay_schet_parts.VyvodId AND vyvod_parts.Status = 1')
            ->where([
                'pay_schet_parts.PartnerId' => $partsBalancePartnerForm->getPartner()->ID,
                'pay_schet.Status' => '1',
            ])
            ->andWhere(['>=', 'pay_schet.DateCreate', strtotime($partsBalancePartnerForm->filters['datefrom'].':00')])
            ->andWhere(['<=', 'pay_schet.DateCreate', strtotime($partsBalancePartnerForm->filters['dateto'])])
            ->andWhere(['not exists', $refundPaySchetSubQuery]);

        $result['recordsTotal'] = $q->count();

        foreach ($partsBalancePartnerForm->columns as $column) {
            if (!in_array($column['name'], array_keys(PartsBalancePartnerForm::COLUMNS_BY_PARTS_BALANCE))) {
                throw new \Exception("Invalid column name: \"{$column['name']}\".");
            }
            if(!empty($column['search']['value'])) {
                $arr = explode(' AS ', $column['name']);
                $q->andWhere([
                    'like',
                    $arr[0],
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
        unset($columns['VyvodDateCreate']);
        $columns = array_keys($columns);
        $columns[] = 'FROM_UNIXTIME(pay_schet.DateCreate) AS DateCreate';
        $columns[] = 'FROM_UNIXTIME(vyvod_parts.DateCreate) AS VyvodDateCreate';

        $columnNOrder = $partsBalancePartnerForm->order[0]['column'];
        $orderColumn = $partsBalancePartnerForm->columns[$columnNOrder]['data'];
        if (!in_array($orderColumn, $this->getValidOrderColumns($columns))) {
            throw new \Exception("Invalid order column name: \"{$orderColumn}\".");
        }
        $orderDir = strtoupper(trim($partsBalancePartnerForm->order[0]['dir']));
        if (!in_array($orderDir, ['ASC', 'DESC', ''])) {
            throw new \Exception("Invalid order direction: \"{$orderDir}\".");
        }
        $q->orderBy($orderColumn.' '.$orderDir);

        $q->addSelect($columns);
        $result['data'] = $q->asArray()->all();
        return $result;
    }

    private function getValidOrderColumns(array $columnNames): array
    {
        $result = [];
        foreach ($columnNames as $columnName) {
            $parts = explode(' AS ', $columnName);
            $result[] = array_pop($parts);
        }
        return $result;
    }
}