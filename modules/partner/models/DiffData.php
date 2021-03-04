<?php

namespace app\modules\partner\models;

use app\services\payment\models\PaySchet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\db\Query;

class DiffData
{
    private $registry;

    public function read($filename)
    {
        $readerType = IOFactory::identify($filename);
        $reader = IOFactory::createReader($readerType);
        $spreadsheet = $reader->load($filename);

        $rows = $spreadsheet->getActiveSheet()->toArray();

        $this->registry = [];

        $n = 0;
        foreach ($rows as $row) {
            if ($n > 2) {
                $this->registry[] = [
                    'ExtBillNumber' => $row[1],
                    'Status' => $row[12],
                ];
            }

            $n++;
        }
    }

    public function execute(): array
    {
        $query = new Query();
        $paySchets = $query->select('*')
            ->from('pay_schet as ps')
            ->where(['in', 'ps.ExtBillNumber', array_column($this->registry, 'ExtBillNumber')])
            ->leftJoin('uslugatovar as ut', 'ut.Id=ps.IdUsluga')
            ->all();

        $map = self::getMap($paySchets);

        return self::getData($this->registry, $map);
    }

    private function getData(array $registry, array $map): array
    {
        $badStatus = [];
        $notFound = [];
        foreach ($registry as $record) {
            if (!array_key_exists($record['ExtBillNumber'], $map)) {
                $notFound[] = $record;
                continue;
            }

            $paySchet = $map[$record['ExtBillNumber']];
            if (PaySchet::STATUSES[$paySchet->Status] !== PaySchet::STATUS_DONE || $record['Status'] !== 'Успешно') {
                $badStatus[] = [
                    'record' => $record,
                    'paySchet' => $paySchet,
                ];
            }
        }

        return [$badStatus, $notFound];
    }

    private function getMap(array $paySchets): array
    {
        $map = [];
        /** @var PaySchet $paySchet */
        foreach ($paySchets as $paySchet) {
            $map[$paySchet['ExtBillNumber']] = $paySchet;
        }

        return $map;
    }
}
