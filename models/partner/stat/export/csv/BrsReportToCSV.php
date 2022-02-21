<?php

namespace app\models\partner\stat\export\csv;

use app\services\payment\models\PaySchet;

class BrsReportToCSV extends OtchToCSV
{
    private const SUM_COL_POSITION = 4;

    protected function header(bool $isAdmin = false): array
    {
        return [];
    }

    protected function listData($list, bool $isAdmin = false): array
    {
        if ((is_array($list) || $list instanceof \Generator) === false) {
            throw new \InvalidArgumentException('list должен быть массивом или генератором');
        }

        $result = [];

        uasort($list, static function(PaySchet $a, PaySchet $b) {
            return $a->DateCreate - $b->DateCreate;
        });

        $listFormatted = [];
        $currentDate = null;

        $dateList = array_unique(array_map(static function($v): string {
            return date('Y-m-d', $v);
        }, array_column($list, 'DateCreate')));

        foreach ($list as $item) {

            if ($currentDate !== date('Y-m-d', $item->DateCreate)) {
                $currentDate = date('Y-m-d', $item->DateCreate);
            }

            $listFormatted[$currentDate][] = $item;
        }

        foreach ($dateList as $date) {
            $result[] = [$date, $listFormatted[$date][0]->GateLogin];
            foreach ($listFormatted[$date] as $v) {
                /** @var PaySchet $v */
                $result[] = [
                    date('Y-m-d', $v->DateCreate),
                    $v->ExtBillNumber,
                    'B2C',
                    $v->GateLogin,
                    $v->getSummFull(),
                    643,
                ];
            }
        }

        return $result;
    }

    protected function preparationData(array $list): \Generator
    {
        $listData = $this->listData($list['data']);

        yield from array_merge(
            [$this->header()],
            $listData,
            $this->totalString($listData, new FooterInfo(['totalCount' => count($list['data'])]))
        );
    }

    protected function totalString(array $list, ?FooterInfo $footerInfo = null): array
    {
        $totalSum = 0;

        foreach ($list as $data) {
            if (array_key_exists(self::SUM_COL_POSITION, $data)) {
                $totalSum += (float) str_replace(',', '.', $data[self::SUM_COL_POSITION]);
            }
        }

        return [
            [
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'Общее количество переводов: ',
                $footerInfo->totalCount > 0 ? $footerInfo->totalCount : count($list),
                '',
                '',
                '',
                '',
            ],
            [
                'Сумма Переводов, руб: ',
                number_format($totalSum, 2, ',', ''),
                '',
                '',
                '',
                '',
            ],
        ];
    }
}
