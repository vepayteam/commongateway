<?php

namespace app\modules\partner\models;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class DiffReadFilter implements IReadFilter {

    public function readCell($column, $row, $worksheetName = '')
    {
        // B - вторая колонка (номер заявки ПЦ)
        // M - тринадцатая колонка (статус)
        return $column === 'B' || $column === 'M';
    }
}
