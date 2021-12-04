<?php


namespace app\services\balance\models;


use app\models\payonline\Partner;
use yii\base\Model;

class PartsBalanceForm extends Model
{

    const COLUMNS_BY_PARTS_BALANCE = [
        'pay_schet.ID AS ID' => 'ID счета',
        'partner.Name AS Name' => 'Имя партнера',
        'CAST(`pay_schet_parts`.`Amount` AS DECIMAL(10,2)) AS Amount' => 'Сумма части, руб',
        'DateCreate AS DateCreate' => 'Дата создания',
        'pay_schet.Extid AS Extid' => 'Extid',
        'ROUND(`pay_schet`.`SummPay` / 100, 2) AS SummPay' => 'Сумма платежа, руб',
        'ROUND(`pay_schet`.`ComissSumm` / 100, 2) AS ComissSumm' => 'Комиссия, руб',
        'ROUND(`pay_schet`.`MerchVozn` / 100, 2) AS MerchVozn' => 'Возн мерчанта, руб',
        'ROUND(`pay_schet`.`BankComis` / 100, 2) AS BankComis' => 'Комисс банка, руб',
        'pay_schet.ErrorInfo AS ErrorInfo' => 'Сообщение',
        'pay_schet.CardNum AS CardNum' => 'Номер карты',
        'pay_schet.CardHolder AS CardHolder' => 'Владелец карты',
        'pay_schet.Dogovor AS Dogovor' => 'Договор',
        'pay_schet.FIO AS FIO' => 'ФИО',

        'vyvod_parts.PayschetId AS PayschetId' => 'ИД платежа вывода',
        'VyvodDateCreate AS VyvodDateCreate' => 'Дата вывода',
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