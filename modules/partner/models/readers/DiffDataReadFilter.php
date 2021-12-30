<?php

namespace app\modules\partner\models\readers;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class DiffDataReadFilter implements IReadFilter {

    /**
     * @inheritdoc
     */
    public function readCell($column, $row, $worksheetName = ''): bool
    {
        return $row >= 2;
    }
}
