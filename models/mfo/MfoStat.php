<?php


namespace app\models\mfo;

use app\models\partner\stat\ExportExcel;
use app\models\partner\stat\PayShetStat;
use app\models\partner\UserLk;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\PaySchet;
use Yii;

class MfoStat
{
    /** Ширина колонок в таблице */
    public const SIZES_ADMIN = [15, 20, 20, 25, 15, 15, 10, 10, 10, 10, 10, 18, 12, 18, 12, 20, 20, 20, 20, 20, 20];
    /** Ширина колонок в таблице */
    public const SIZES_USER = [15, 20, 20, 25, 15, 15, 10, 10, 10, 18, 12, 18, 12, 20, 20, 20, 20, 20, 20];
    public const ITOGS_ADMIN = [6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1];
    /** For excel we need move ITOGO ot one cell right */
    public const ITOGS_ADMIN_EXCEL = [7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1];
    public const ITOGS_USER = [6 => 1, 7 => 1, 8 => 1];
    /** For excel we need move ITOGO ot one cell right */
    public const ITOGS_USER_EXCEL = [7 => 1, 8 => 1, 9 => 1];
    public const HEAD_ADMIN = [
        'ID Vepay', 'ExtID', 'Код ответа', 'Услуга', 'Реквизиты', 'Договор', 'ФИО', 'Сумма', 'Комиссия', 'К оплате', 'Валюта',
        'Комис. банка', 'Возн. Vepay', 'Дата создания', 'Статус', 'Ошибка', 'Дата оплаты', 'Номер транзакции',
        'ID мерчанта', 'Тип карты', 'Маска карты', 'Держатель карты', 'RRN', 'Хэш от номера карты', 'Маска карты получателя', 'Наименование банка-эквайера',
    ];
    public const HEAD_USER = [
        'ID Vepay', 'ExtID', 'Код ответа', 'Услуга', 'Реквизиты', 'Договор', 'ФИО', 'Сумма', 'Комиссия', 'К оплате', 'Валюта',
        'Дата создания', 'Статус', 'Ошибка', 'Дата оплаты', 'Номер операции',
        'ID мерчанта', 'Тип карты', 'Маска карты', 'Держатель карты', 'RRN', 'Хэш от номера карты', 'Маска карты получателя', 'Наименование банка-эквайера',
    ];

    public function ExportOpList($post)
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        $payShetList = new PayShetStat();
        $payShetList->load($post, '');
        $list = $payShetList->getList($IsAdmin, 0, null);

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
                    PaySchet::getStatusTitle($row['Status']),
                    $row['ErrorInfo'],
                    $row['DateOplat'] > 0 ? date("d.m.Y H:i:s", $row['DateOplat']) : '',
                    $row['ExtBillNumber'],
                    $row['IdOrg'],
                    $row['CardType'],
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
                    PaySchet::getStatusTitle($row['Status']),
                    $row['ErrorInfo'],
                    $row['DateOplat'] > 0 ? date("d.m.Y H:i:s", $row['DateOplat']) : '',
                    $row['IdOrg'],
                    $row['CardType'],
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

    /**
     * Костыльный метод для формирования итогов xls выгрузки
     * тк итоги высчитываются в {@see PayShetStat::getList2()} считать их заново не имеет смысла
     *
     * TODO убрать при переписывании списка операций и выгрузок
     *
     * @param $data
     * @param bool $isAdmin
     * @return string[]
     */
    public static function getOperationListResultRow($data, bool $isAdmin): array
    {
        $result = [
            'ИТОГО:',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $result[] = PaymentHelper::convertToFullAmount($data['sumpay']);
        $result[] = PaymentHelper::convertToFullAmount($data['sumcomis']);
        $result[] = PaymentHelper::convertToFullAmount($data['sumpay'] + $data['sumcomis']);

        if ($isAdmin) {
            $result[] = PaymentHelper::convertToFullAmount($data['bankcomis']);
            $result[] = PaymentHelper::convertToFullAmount($data['voznagps']);
        }

        return $result;
    }

    public static function getDataGenerator(\Generator $data, ?bool $isAdmin): \Generator
    {
        foreach ($data as $row) {

                $retAdmin = $isAdmin ? [
                    ($row['BankComis'] / 100.0),
                    $row['VoznagSumm'] / 100.0,
                ] : [];

                yield array_merge(
                    [
                        $row['ID'],
                        $row['Extid'],
                        $row['RCCode'],
                        $row['NameUsluga'],
                        $row['QrParams'],
                        $row['Dogovor'],
                        $row['FIO'],
                        $row['SummPay'] / 100.0,
                        $row['ComissSumm'] / 100.0,
                        ($row['SummPay'] + $row['ComissSumm']) / 100.0,
                        $row['Currency'],
                    ],
                    $retAdmin,
                    [
                        date("d.m.Y H:i:s", $row['DateCreate']),
                        PaySchet::getStatusTitle($row['Status']),
                        $row['ErrorInfo'],
                        $row['DateOplat'] > 0 ? date("d.m.Y H:i:s", $row['DateOplat']) : '',
                        $row['ExtBillNumber'],
                        $row['IdOrg'],
                        $row['CardType'],
                        $row['CardNum'],
                        $row['CardHolder'],
                        $row['RRN'],
                        $row['IdKard'],
                        $row['OutCardPan'],
                        $row['BankName'],
                    ]
                );
        }
    }

    /**
     * Получение отчёта с использованием "сырой" записи в xls, в обход PHPSpreadsheet, для сохранения больших XLS-файлов
     *
     * @TODO: Probably @deprecated
     *
     * @param array $input
     */
    public function ExportOpListRaw(array $input): void
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        $paySchetList = new PayShetStat();
        $paySchetList->load($input, '');
        $list = $paySchetList->getList($IsAdmin, 0, null, true);

        $data = self::getDataGenerator($list['data'], $IsAdmin);

        if ($IsAdmin) {
            $head = self::HEAD_ADMIN;
            $totals = self::ITOGS_ADMIN;
        } else {
            $head = self::HEAD_USER;
            $totals = self::ITOGS_USER;
        }

        $ExportExcel = new ExportExcel();
        $ExportExcel->CreateXlsRaw("Экспорт", $head, $data, $totals);
    }
}
