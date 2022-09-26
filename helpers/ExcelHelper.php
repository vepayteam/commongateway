<?php

namespace app\helpers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Html;

class ExcelHelper
{
    public static function generateFromHtml(string $html): string
    {
        $reader = new Html();
        $spreadsheet = $reader->loadFromString($html);
        /**
         * Fix column width
         * @link https://stackoverflow.com/a/26214779/1215728
         */
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($worksheet));
            $sheet = $spreadsheet->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }

        // Save to a temporary file and return its content
        $tmpdir = \Yii::getAlias('@runtime') . '/tmp';
        if (!file_exists($tmpdir)) {
            mkdir($tmpdir, 0777, true);
        }
        $tmpfile = tempnam($tmpdir, 'excel');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmpfile);
        $content = file_get_contents($tmpfile);
        unlink($tmpfile);

        return $content;
    }
}