<?php

namespace app\models\partner\stat;

use app\models\partner\stat\export\phpExportData\ExportDataExcel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;

class ExportExcel
{
    /**
     * @param string     $title
     * @param array      $head
     * @param \Generator $data
     * @param array      $totalRules
     */
    public function CreateXlsRaw(string $title, array $head, \Generator $data, array $totalRules = []): void
    {
        $totals = ['ИТОГО:'];

        // инициируем класс для "низкоуровневой" записи в XLS-файл
        $exporter = new ExportDataExcel('browser', 'export.xls');
        $exporter->title = $title;
        $exporter->initialize();

        // шапка
        $exporter->addRow($head);

        $rowCellCount = null;

        // данные
        foreach ($data as $row) {

            if ($rowCellCount === null) {
                $rowCellCount = count($row);
            }

            $exporter->addRow($row);

            foreach ($row as $i => $item) {
                if (isset($totalRules[$i])) {
                    @$totals[$i] += $item;
                }
            }
        }

        //итого
        if (count($totals) > 0) {

            for ($i = 1; $i < $rowCellCount; $i++) {
                $totals[$i] = (array_key_exists($i, $totals) ? $totals[$i] : '');
            }

            ksort($totals);

            $exporter->addRow($totals);
        }

        $exporter->finalize();

        exit;
    }

    public function CreateXls($title, $head, $data, $sizes, $itogs = [])
    {
        $GapXLS = 1;   //текущая строка
        try {
            $itogsVal = [];
            $objPHPExcel = new Spreadsheet();

            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle($title);

            //размер столбцов
            foreach ($sizes as $i => $item) {
                $sheet->getColumnDimension(self::xl($i))->setWidth($item);
            }

            //заголовок таблицы
            foreach ($head as $i => $item) {
                $sheet->getStyle(self::xl($i) . "1")->getFont()->setBold(true);
                $sheet->SetCellValue(self::xl($i) . $GapXLS, $item);
            }

            $GapXLS++;

            //данные
            foreach ($data as $row) {
                foreach ($row as $i => $item) {
                    $sheet->SetCellValue(self::xl($i) . $GapXLS, $item);
                    if (isset($itogs[$i])) {
                        @$itogsVal[$i] += $item;
                    }
                }
                $GapXLS++;
            }

            //итого
            if (count($itogsVal)) {
                foreach ($itogsVal as $i => $item) {
                    $sheet->SetCellValue(self::xl($i) . $GapXLS, $item);
                }
                $sheet->SetCellValue(self::xl(0) . $GapXLS, "ИТОГО:");
                //$GapXLS++;
            }

            //Сохранение в файл
            $tmpdir = implode(DIRECTORY_SEPARATOR, [
                Yii::getAlias('@runtime'),
                'tmp',
            ]);

            if(!file_exists($tmpdir)) {
                mkdir($tmpdir, 0777, true);
            }

            $tmpfile = implode(DIRECTORY_SEPARATOR, [
                $tmpdir,
                Yii::$app->security->generateRandomString() . '.xlsx',
            ]);
            $writer = IOFactory::createWriter($objPHPExcel, 'Xlsx');
            $writer->save($tmpfile);

            $data = file_get_contents($tmpfile);
            unlink($tmpfile);

            return $data;

        } catch(\PhpOffice\PhpSpreadsheet\Exception $e){
            echo $e->__toString();
        }

        return false;
    }

    public static function xl($inxd)
    {
        $Alpha = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
        if ($inxd < count($Alpha)) {
            return $Alpha[$inxd];
        } else {
            $ret = "";
            $ix = $inxd;
            do {
                $ost = $ix % count($Alpha);
                $ix = $ix / count($Alpha);
                $ret = $Alpha[$ost] . $ret;
            } while ($ix > count($Alpha));
            $ret = $Alpha[$ix - 1] . $ret;
            return $ret;
        }
    }
}
