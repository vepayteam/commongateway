<?php


namespace app\models\partner\stat\export\excel;


use app\models\partner\stat\export\excel\cells\ICell;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use yii\helpers\VarDumper;

class Line
{

    private $line;

    /**
     * @param ICell[] $cells
    */
    public function __construct(array $cells) {
        $this->line = $cells;
    }

    /**
    * @return ICell[]
    */
    public function cells(): array{
        return $this->line;
    }
}