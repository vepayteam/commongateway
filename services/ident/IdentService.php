<?php


namespace app\services\ident;


use app\services\ident\models\IdentStatisticForm;
use app\services\ident\traits\RunaIdentTrait;
use yii\db\Query;

class IdentService
{
    use RunaIdentTrait;

    const LIST_CHECKS = ['Inn', 'Snils', 'Passport', 'PassportDeferred'];
    const BANK_STATUSES = [
        'NotProcessed' => '000',
        'Processing' => '001',
        'Error' => '010',
        'DataMissing' => '011',
        'Valid' => '100',
        'NotValid' => '101',
    ];

    public function getIdentStatistic(IdentStatisticForm $identStatisticForm)
    {
        $result = [
            'draw' => $identStatisticForm->draw,
        ];

        $q = new Query();
        $q
            ->from('user_identification')
            ->where([
                'IdOrg' => $identStatisticForm->getPartner()->ID,
            ])
            ->andWhere(['>=', 'user_identification.DateCreate', strtotime($identStatisticForm->filters['datefrom'] . ':00')])
            ->andWhere(['<=', 'user_identification.DateCreate', strtotime($identStatisticForm->filters['dateto'])]);


        $result['recordsTotal'] = $q->count();

        foreach ($identStatisticForm->columns as $column) {
            if (!empty($column['search']['value'])) {
                $arr = explode(' AS ', $column['name']);
                $q->andWhere([
                    'like',
                    $arr[0],
                    $column['search']['value']
                ]);
            }
        }

        $result['recordsFiltered'] = $q->count();

        $q->limit($identStatisticForm->length);
        $q->offset($identStatisticForm->start);

        // подмена даты
        $columns = IdentStatisticForm::COLUMNS_BY_PARTS_BALANCE;
        unset($columns['DateCreate']);
        $columns = array_keys($columns);
        $columns[] = 'FROM_UNIXTIME(user_identification.DateCreate) AS DateCreate';

        $columnNOrder = $identStatisticForm->order[0]['column'];
        $orderColumn = $identStatisticForm->columns[$columnNOrder]['data'];
        $orderDir = $identStatisticForm->order[0]['dir'];
        $q->orderBy($orderColumn . ' ' . $orderDir);

        $q->addSelect($columns);
        $result['data'] = $q->all();
        return $result;

    }

    /**
     * @param array $response
     * @return int
     */
    public function getCheckStatusByStateResponse(array $response)
    {
        $decResult = '';
        foreach (self::LIST_CHECKS as $listCheck) {
            if (
                array_key_exists($listCheck, $response)
                && array_key_exists('Status', $response[$listCheck])
                && in_array($response[$listCheck]['Status'], array_keys(self::BANK_STATUSES))
            ) {
                $key = $response[$listCheck]['Status'];
                $decResult .= self::BANK_STATUSES[$key];
            } else {
                $decResult .= self::BANK_STATUSES['DataMissing'];
            }
        }

        return bindec($decResult);
    }

}
