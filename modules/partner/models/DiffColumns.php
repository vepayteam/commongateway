<?php

namespace app\modules\partner\models;

class DiffColumns
{
    private $dbColumns = [
        'ID', 'RRN', 'ExtBillNumber'
    ];

    public function getDbColumns(): array
    {
        return $this->dbColumns;
    }
}
