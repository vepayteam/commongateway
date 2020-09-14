<?php


namespace app\services\balance\models;


use app\models\payonline\Partner;
use yii\base\Model;

class PartsBalanceForm extends Model
{

    const COLUMNS_BY_PARTS_BALANCE = [
        'pay_schet.ID' => 'ID счета',
        'partner.Name' => 'Имя партнера',
        'pay_schet_parts.Amount' => 'Сумма части',
        'DateCreate' => 'Дата создания',
        'pay_schet.Extid' => 'Extid',
        'pay_schet.SummPay' => 'Сумма платежа, коп',
        'pay_schet.ComissSumm' => 'Комиссия, коп',
        'pay_schet.MerchVozn' => 'Возн мерчанта, коп',
        'pay_schet.BankComis' => 'Комисс банка, коп',
        'pay_schet.ErrorInfo' => 'Сообщение',
        'pay_schet.CardNum' => 'Номер карты',
        'pay_schet.CardHolder' => 'Владелец карты',
        'pay_schet.Dogovor' => 'Договор',
        'pay_schet.FIO' => 'ФИО',
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
            $arr = explode('.', $k);

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
