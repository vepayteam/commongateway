<?php

namespace app\modules\partner\models;

use app\services\payment\models\PaySchet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\db\Query;

class DiffData
{
    private const CSV_READER_TYPE = 'Csv';
    private const CSV_DELIMITER = ';';

    private $registry;

    public function read($filename)
    {
        $fileFormat = IOFactory::identify($filename);
        Yii::warning('Stat diffData file format ' . $filename
            . ': format=' . $fileFormat
        );

        $reader = IOFactory::createReader($fileFormat);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter(new DiffReadFilter());

        if ($fileFormat === self::CSV_READER_TYPE) {
            $reader->setDelimiter(self::CSV_DELIMITER);
        }

        $spreadsheet = $reader->load($filename);
        Yii::warning('Stat diffData spreadsheet loaded ' . $filename);

        $rows = $spreadsheet->getActiveSheet()->toArray();
        Yii::warning('Stat diffData rows loaded' . $filename
            . ': rows_count=' . count($rows)
        );

        $this->registry = [];

        $n = 0;
        foreach ($rows as $row) {
            if ($n > 2) {
                $this->registry[] = [
                    'ExtBillNumber' => strval($row[1]),
                    'Status' => $row[12],
                ];
            }

            $n++;
        }

        Yii::warning('Stat diffData registry ready: count=' . count($this->registry));
    }

    public function execute(): array
    {
        $query = new Query();
        $paySchets = $query->select(['ps.ID', 'ps.Extid', 'ps.Status', 'ps.DateCreate', 'ps.DateOplat', 'ps.ExtBillNumber', 'ut.NameUsluga'])
            ->from('pay_schet as ps')
            ->where(['in', 'ps.ExtBillNumber', array_column($this->registry, 'ExtBillNumber')])
            ->leftJoin('uslugatovar as ut', 'ut.Id=ps.IdUsluga')
            ->all();

        Yii::warning('Stat diffData paySchets loaded: count=' . count($paySchets));

        $map = self::getMap($paySchets);
        Yii::warning('Stat diffData map ready: count=' . count($map));

        return self::getData($map);
    }

    private function getData(array $map): array
    {
        $badStatus = [];
        $notFound = [];
        foreach ($this->registry as $record) {
            if (!array_key_exists($record['ExtBillNumber'], $map)) {
                $notFound[] = $record;
                continue;
            }

            /** @var PaySchet $paySchet */
            $paySchet = $map[$record['ExtBillNumber']];
            if (intval($paySchet['Status']) !== PaySchet::STATUS_DONE || $record['Status'] !== 'Успешно') {
                $badStatus[] = [
                    'record' => $record,
                    'paySchet' => $paySchet,
                ];
            }
        }

        Yii::warning('Stat diffData data ready: badStatus=' . count($badStatus)
            . ' notFound=' . count($notFound)
        );

        return [$badStatus, $notFound];
    }

    private function getMap(array $paySchets): array
    {
        $map = [];
        foreach ($paySchets as $paySchet) {
            $map[$paySchet['ExtBillNumber']] = $paySchet;
        }

        return $map;
    }
}
