<?php


namespace app\models\partner\callback;

use app\models\partner\stat\exceptions\ExportExcelRawException;
use app\models\partner\stat\ExportExcel;
use app\models\partner\UserLk;
use app\services\payment\models\PaySchet;
use Yii;
use yii\web\Response;

class CallbackStat
{
    /** Ширина колонок в таблице */
    const SIZES_ADMIN = [15, 25, 35, 25, 35];
    /** Ширина колонок в таблице */
    const SIZES_USER = [15, 25, 35, 25, 35];

    const HEAD_ADMIN = [
        'Операция', 'Дата создания', 'ExtId', 'Адрес запроса', 'Дата выполнения', 'Результат',
    ];
    const HEAD_USER  = [
        'Операция', 'Дата создания', 'Адрес запроса', 'Дата выполнения', 'Результат',
    ];

    /**
     * @param \Generator $data
     *
     * @return \Generator
     */
    private static function getDataGenerator(\Generator $data, bool $IsAdmin): \Generator
    {
        foreach ($data as $k => $row) {
            yield ($IsAdmin ? [
                $row['IdPay'],
                date("d.m.Y H:i:s", $row['DateCreate']),
                $row['Extid'],
                !empty($row['FullReq']) ? $row['FullReq'] : $row['Email'],
                $row['DateSend'] > 1 ? date("d.m.Y H:i:s", $row['DateSend']) : 'в очереди',
                $row['HttpCode'] . ': ' . $row['HttpAns'],
            ] : [
                $row['IdPay'],
                date("d.m.Y H:i:s", $row['DateCreate']),
                !empty($row['FullReq']) ? $row['FullReq'] : $row['Email'],
                $row['DateSend'] > 1 ? date("d.m.Y H:i:s", $row['DateSend']) : 'в очереди',
                $row['HttpCode'] . ': ' . $row['HttpAns'],
            ]);
        }
    }

    /**
     * Получение отчёта с использованием "сырой" записи в xls, в обход PHPSpreadsheet, для сохранения больших XLS-файлов
     *
     * @param array $input
     * @throws ExportExcelRawException
     */
    public function ExportOpListRaw(array $input): void
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $IsAdmin = (bool) UserLk::IsAdmin(Yii::$app->user);

        $CallbackList = new CallbackList();
        $CallbackList->load($input, '');
        $list = $CallbackList->GetList($IsAdmin, 0, true, true);

        $data = self::getDataGenerator($list['data'], $IsAdmin);

        $head = $IsAdmin === true ? self::HEAD_ADMIN : self::HEAD_USER;

        $ExportExcel = new ExportExcel();
        $ExportExcel->CreateXlsRaw("Экспорт", $head, $data, []);
    }
}
