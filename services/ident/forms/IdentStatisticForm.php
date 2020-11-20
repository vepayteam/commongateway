<?php


namespace app\services\ident\forms;


use app\models\payonline\Partner;
use yii\base\Model;

class IdentStatisticForm extends Model
{
    const COLUMNS_BY_PARTS_BALANCE = [
        'DateCreate AS DateCreate' => 'Дата запроса',
        'user_identification.ID AS ID' => 'ID',
        'user_identification.TransNum AS TransNum' => 'Код транзакции',
        'user_identification.Name AS Name' => 'Имя',
        'user_identification.Fam AS Fam' => 'Фамилия',
        'user_identification.Otch AS Otch' => 'Отчество',
        'user_identification.Inn AS Inn' => 'ИНН',
        'user_identification.Snils AS Snils' => 'СНИЛС',
        'user_identification.PaspSer AS PaspSer' => 'Паспорт серия',
        'user_identification.PaspNum AS PaspNum' => 'Паспорт номер',
        'user_identification.PaspPodr AS PaspPodr' => 'Паспорт подразд.',
        'user_identification.PaspDate AS PaspDate' => 'Паспорт дата',
        'user_identification.PaspVidan AS PaspVidan' => 'Папорт выдан',
    ];

    private $partner;

    public $draw;
    public $columns;
    public $length;
    public $order;
    public $start;
    public $filters;

    public function rules()
    {
        return [
            [['draw', 'columns', 'length', 'order', 'start', 'filters'], 'required'],
            ['filters', 'validatePartner'],
        ];
    }

    /**
     * @return bool
     */
    public function validatePartner()
    {
        return !empty($this->getPartner());
    }

    /**
     * @return Partner|null
     */
    public function getPartner()
    {
        if(!$this->partner) {
            $this->partner = Partner::findOne(['ID' => $this->filters['partnerId']]);
        }
        return $this->partner;
    }

    /**
     * @return array
     */
    public static function getDatatableColumns()
    {
        $result = [];
        foreach (self::COLUMNS_BY_PARTS_BALANCE as $k => $name) {
            $arr = explode(' AS ', $k);

            $dataName = $k;
            if(count($arr) == 2) {
                $dataName = $arr[1];
            }

            $result[] = [
                'data' => $dataName,
                'name' => $k,
                'title' => $name,
            ];
        }
        return $result;
    }
}
