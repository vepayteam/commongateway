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
    /** Ширина колонок в таблице */
    const SIZES_ADMIN = [15, 20, 20, 25, 15, 15, 10, 10, 10, 10, 10, 18, 12, 18, 12, 20, 20, 20, 20, 20, 20];
    /** Ширина колонок в таблице */
    const SIZES_USER = [15, 20, 20, 25, 15, 15, 10, 10, 10, 18, 12, 18, 12, 20, 20, 20, 20, 20, 20];

    const ITOGS_ADMIN = [6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1];
    const ITOGS_USER = [6 => 1, 7 => 1, 8 => 1];

    const HEAD_ADMIN = [
        'ID Vepay', 'ExtID', 'Услуга', 'Реквизиты', 'Договор', 'ФИО', 'Сумма', 'Комиссия', 'К оплате',
        'Комис. банка', 'Возн. Vepay', 'Дата создания', 'Статус', 'Дата оплаты', 'Номер транзакции',
        'ID мерчанта', 'Маска карты', 'Держатель карты', 'RRN', 'Хэш от номера карты', 'Наименование банка-эквайера',
    ];
    const HEAD_USER = [
        'ID Vepay', 'ExtID', 'Услуга', 'Реквизиты', 'Договор', 'ФИО', 'Сумма', 'Комиссия', 'К оплате',
        'Дата создания', 'Статус', 'Дата оплаты', 'Номер операции',
        'ID мерчанта', 'Маска карты', 'Держатель карты', 'RRN', 'Хэш от номера карты', 'Наименование банка-эквайера',
    ];

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
                    $row['ExtBillNumber'],
                    $row['IdOrg'],
                    $row['CardNum'],
                    $row['CardHolder'],
                    $row['RRN'],
                    $row['IdKard'],
                    $row['BankName'],

                ];
            }

            $head = self::HEAD_ADMIN;
            $sizes = self::SIZES_ADMIN;
            $itogs = self::ITOGS_ADMIN;

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
                    $row['IdOrg'],
                    $row['CardNum'],
                    $row['CardHolder'],
                    $row['RRN'],
                    $row['IdKard'],
                    $row['BankName'],
                ];
            }

            $head = self::HEAD_USER;
            $sizes = self::SIZES_USER;
            $itogs = self::ITOGS_USER;
        }

        $ExportExcel = new ExportExcel();
        return $ExportExcel->CreateXls("Экспорт", $head, $data, $sizes, $itogs);
    }
}