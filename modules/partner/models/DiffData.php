<?php

namespace app\modules\partner\models;

use app\modules\partner\models\readers\DiffDataReadFilter;
use app\services\payment\models\PaySchet;
use Yii;
use yii\db\Query;

class DiffData
{
    /**
     * @var DiffDataForm
     */
    private $form;

    /**
     * @param DiffDataForm $form
     */
    public function __construct(DiffDataForm $form)
    {
        $this->form = $form;
    }

    /**
     * @return array[]
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function execute(): array
    {
        $registry = $this->getRegistry();

        $query = new Query();
        $paySchets = $query->select(['ps.ID', 'ps.Extid', 'ps.Status', 'ps.DateCreate', 'ps.DateOplat', 'ps.ExtBillNumber', 'ps.RRN', 'ut.NameUsluga'])
            ->from('pay_schet as ps')
            ->where(['Bank' => $this->form->bank])
            ->andWhere(['in', 'ps.' . $this->form->dbColumn, array_column($registry, 'Select')])
            ->leftJoin('uslugatovar as ut', 'ut.Id=ps.IdUsluga')
            ->all();

        Yii::info('Stat diffData paySchets loaded: count=' . count($paySchets));

        $map = $this->getMap($paySchets);

        return self::getData($registry, $map);
    }

    /**
     * @param array $registry
     * @param array $map
     * @return array[]
     */
    private function getData(array $registry, array $map): array
    {
        $badStatus = [];
        $notFound = [];
        foreach ($registry as $record) {
            if (!array_key_exists($record['Select'], $map)) {
                $notFound[] = $record;
                continue;
            }

            /** @var PaySchet $paySchet */
            $paySchet = $map[$record['Select']];

            if ($this->form->allRegistryStatusSuccess) {
                if (intval($paySchet['Status']) !== PaySchet::STATUS_DONE) {
                    $badStatus[] = [
                        'record' => $record,
                        'paySchet' => $paySchet,
                    ];
                }
            } else {
                $paySchetStatus = $paySchet['Status'];
                $registryStatus = $this->form->getStatusFor($paySchetStatus);
                if ($record['Status'] !== $registryStatus) {
                    $badStatus[] = [
                        'record' => $record,
                        'paySchet' => $paySchet,
                    ];
                }
            }
        }

        Yii::info('Stat diffData data ready: badStatus=' . count($badStatus)
            . ' notFound=' . count($notFound)
        );

        return [$badStatus, $notFound];
    }

    /**
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function getRegistry(): array
    {
        $reader = new DiffReader($this->form->registryFile->tempName);
        $rows = $reader->readActiveSheet(new DiffDataReadFilter());

        $registry = [];

        foreach ($rows as $row) {
            $select = strval($row[$this->form->registrySelectColumn]);
            $status = $this->form->allRegistryStatusSuccess ? PaySchet::STATUSES[PaySchet::STATUS_DONE] : strval($row[$this->form->registryStatusColumn]);

            if (empty($select) || empty($status)) {
                continue;
            }

            $registry[] = [
                'Select' => $select,
                'Status' => $status,
            ];
        }

        Yii::info('Stat diffData registry ready: count=' . count($registry));

        return $registry;
    }

    /**
     * @param array $paySchets
     * @return array
     */
    private function getMap(array $paySchets): array
    {
        $map = [];
        foreach ($paySchets as $paySchet) {
            $map[$paySchet[$this->form->dbColumn]] = $paySchet;
        }

        return $map;
    }
}
