<?php

namespace app\modules\partner\models\readers;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class DiffReadColumnsFilter implements IReadFilter
{
    /**
     * @inheritdoc
     */
    public function readCell($column, $row, $worksheetName = ''): bool
    {
        return $row === 1;
    }
}
