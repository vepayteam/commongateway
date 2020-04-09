<?php


namespace app\models\mfo;

use app\models\partner\stat\ExportExcel;
use app\models\partner\stat\PayShetStat;
use app\models\partner\UserLk;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use Yii;

class MfoStat
{
    public function ExportOpList($post)
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        $payShetList = new PayShetStat();
        $payShetList->load($post, '');
        $list = $payShetList->getList($IsAdmin, 0, 1);

        $st = [0 => "Создан", 1 => "Оплачен", 2 => "Отмена", 3 => "Возврат"];
        $data = [];

        if ($IsAdmin) {
            foreach ($list['data'] as $row) {
                $data[] = [
                    $row['ID'],
                    $row['Extid'],
                    $row['NameUsluga'],
                    $row['QrParams'],
                    $row['Dogovor'],
                    $row['FIO'],
                    $row['SummPay'] / 100.0,
                    $row['ComissSumm'] / 100.0,
                    ($row['SummPay'] + $row['ComissSumm']) / 100.0,
                    $row['BankComis'] / 100.0,
                    $row['VoznagSumm'] / 100.0,
                    date("d.m.Y H:i:s", $row['DateCreate']),
                    $st[$row['Status']],
                    $row['DateOplat'] > 0 ? date("d.m.Y H:i:s", $row['DateOplat']) : '',
                    $row['ExtBillNumber']
                ];
            }

            $head = ['ID Vepay', 'ExtID', 'Услуга', 'Реквизиты', 'Договор', 'ФИО', 'Сумма', 'Комиссия', 'К оплате', 'Комис. банка', 'Возн. Vepay', 'Дата создания', 'Статус', 'Дата оплаты', 'Номер транзакции'];

            $sizes = [15, 20, 20, 25, 15, 15, 10, 10, 10, 10, 10, 18, 12, 18, 12];
            $itogs = [6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1];

        } else {
            foreach ($list['data'] as $row) {
                $data[] = [
                    $row['ID'],
                    $row['Extid'],
                    $row['NameUsluga'],
                    $row['QrParams'],
                    $row['Dogovor'],
                    $row['FIO'],
                    $row['SummPay'] / 100.0,
                    $row['ComissSumm'] / 100.0,
                    ($row['SummPay'] + $row['ComissSumm']) / 100.0,
                    date("d.m.Y H:i:s", $row['DateCreate']),
                    $st[$row['Status']],
                    $row['DateOplat'] > 0 ? date("d.m.Y H:i:s", $row['DateOplat']) : '',
                    $row['ExtBillNumber']
                ];
            }

            $head = ['ID Vepay', 'ExtID', 'Услуга', 'Реквизиты', 'Договор', 'ФИО', 'Сумма', 'Комиссия', 'К оплате', 'Дата создания', 'Статус', 'Дата оплаты', 'Номер операции'];

            $sizes = [15, 20, 20, 25, 15, 15, 10, 10, 10, 18, 12, 18, 12];
            $itogs = [6 => 1, 7 => 1, 8 => 1];
        }

        $ExportExcel = new ExportExcel();
        return $ExportExcel->CreateXls("Экспорт", $head, $data, $sizes, $itogs);
    }
}