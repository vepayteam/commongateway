<?php


namespace app\models\partner\stat\export\csv;


use app\models\TU;
use Yii;
use yii\helpers\VarDumper;

/**
 * @property array $list - [ строка1 -> ["col1", "col2", ...], строка2 -> ["col1", "col2", ...]]
 */
class OtchToCSV extends ToCSV
{
    private $payment;
    private $repayment;

    public function __construct(array $list, $payment = null, $repayment = null)
    {
        $this->payment = $payment;
        $this->repayment = $repayment;
        $path = Yii::getAlias('@app/runtime/csv/');
        $filename = time() . '.csv';
        $list = $this->preparationData($list);
        array_unshift($list, $this->header());
        parent::__construct($list, $path, $filename);
    }

    private function header(): array
    {
        return [
            'ID Vepay',
            'ExtID',
            'Услуга',
            'Реквизиты',
            'Договор',
            'ФИО',
            'Сумма',
            'Комиссия',
            'Дата создания',
            'Статус',
            'Дата оплаты',
            'Номер транзакции',
            'ID мерчанта',
            'Маска карты',
            'Держатель карты',
            'RRN',
            'Хэш от номера карты',
            'Наименование банка-эквайера',
        ];
    }

    private function preparationData(array $list): array
    {
        $ret = [];
        $st = [0 => "Создан", 1 => "Оплачен", 2 => "Отмена", 3 => "Возврат"];
        foreach ($list['data'] as $data) {
            if($this->checkData($data)){
                $ret[] = [
                    $data['ID'],
                    $data['Extid'],
                    str_replace('"', "", $data['NameUsluga']),
                    $data['QrParams'],
                    $data['Dogovor'],
                    $data['FIO'],
                    number_format($data['SummPay'] / 100.0, 2, ',', ''),
                    number_format($data['ComissSumm'] / 100.0, 2, ',', ''),
                    date("d.m.Y H:i:s", $data['DateCreate']),
                    $st[$data['Status']],
                    $data['DateOplat'] > 0 ? date("d.m.Y H:i:s", $data['DateOplat']) : '',
                    $data['ExtBillNumber'],
                    $data['IdOrg'],
                    $data['CardNum'],
                    $data['CardHolder'],
                    $data['RRN'],
                    $data['IdKard'],
                    $data['BankName'],
                ];
            }
        }
        array_push($ret, $this->totalString($ret));
        return $ret;
    }

    public function export()
    {
        parent::export();
    }

    private function totalString(array $list): array
    {
        $totalSum = $totalFee = $totalReward = 0;
        $t = [];
        foreach ($list as $data) {
            $totalSum += (float)str_replace(',', '.', $data[6]);
            $totalFee += (float)str_replace(',', '.', $data[7]);
        }
        return [
            'Итого: ',
            '',
            '',
            '',
            '',
            '',
            number_format($totalSum, 2, ',', ''),
            number_format($totalFee, 2, ',', ''),
            '',
            '',
            '',
        ];
    }

    private function checkData(array $data): bool
    {
        if ($this->payment === null && $this->repayment === null){ //в случае когда делается обычный экспорт.
            return true;
        }
        $cust = $data['IsCustom'];
        //если стоит задача отправить "выдачу" и (тип операции выдача на карту или тип операции выдача на счет)
        if ($this->payment && ($cust == TU::$TOCARD || $cust == TU::$TOSCHET)) {
            return true;
        }
        //если стоит задача отправить "погашшение" и (тип операции погашение AFT или погашение ECOM).
        if ($this->repayment && ($cust == TU::$POGASHATF || TU::$POGASHECOM || TU::$AVTOPLATECOM || TU::$AVTOPLATATF)) {
            return true;
        }
        return false;
    }
}