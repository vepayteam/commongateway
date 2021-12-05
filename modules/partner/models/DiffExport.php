<?php

namespace app\modules\partner\models;

use app\models\partner\stat\export\csv\ToCSV;
use app\models\partner\stat\ExportExcel;
use app\services\payment\models\PaySchet;
use Yii;

class DiffExport
{
    const XLSX_DEFAULT_TITLE = 'Лист1';
    const XLSX_DEFAULT_SIZES = [12, 45, 22, 35, 35, 40, 40];

    private $badStatus;
    private $notFound;

    private $rows;

    private $header = [
        'ID Vepay',
        'Ext ID',
        'Номер операции',
        'Статус в Vepay',
        'Статус в банке-эквайере',
        'Дата и время операции',
        'Услуга',
    ];

    public function __construct(array $badStatus, array $notFound)
    {
        $this->badStatus = $badStatus;
        $this->notFound = $notFound;
    }

    public function loadData()
    {
        $this->rows = [];
        foreach ($this->badStatus as $row) {
            $dateCreate = date('d.m.Y H:i:s', $row['paySchet']['DateCreate']);
            $dateOplat = $row['paySchet']['DateOplat'] > 0 ? date('d.m.Y H:i:s', $row['paySchet']['DateOplat']) : 'нет';

            $this->rows[] = [
                $row['paySchet']['ID'],
                $row['paySchet']['Extid'],
                $row['paySchet']['ExtBillNumber'],
                PaySchet::STATUSES[$row['paySchet']['Status']],
                $row['record']['Status'],
                "$dateCreate / $dateOplat",
                $row['paySchet']['NameUsluga'],
            ];
        }

        $this->rows[] = [];
        $this->rows[] = ['Номера заявки ПЦ нет в Vepay'];

        foreach ($this->notFound as $row) {
            $this->rows[] = [$row['Identifier']];
        }
    }

    public function exportCsv()
    {
        $tmpdir = implode(DIRECTORY_SEPARATOR, [
            Yii::getAlias('@runtime'),
            'tmp',
        ]);

        if (!file_exists($tmpdir)) {
            mkdir($tmpdir, 0777, true);
        }

        array_unshift($this->rows, $this->header);
        $toCSV = new ToCSV($this->getGenerator($this->rows), $tmpdir, time() . '.csv');
        $toCSV->export();

        $data = file_get_contents($toCSV->fullpath());
        unlink($toCSV->fullpath());

        return $data;
    }

    public function exportXlsx()
    {
        $export = new ExportExcel();
        return $export->CreateXls(self::XLSX_DEFAULT_TITLE, $this->header, $this->rows, self::XLSX_DEFAULT_SIZES);
    }

    private function getGenerator(array $rows): \Generator
    {
        foreach ($rows as $row) {
            yield $row;
        }
    }
}
