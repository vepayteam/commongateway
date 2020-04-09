<?php


namespace app\models\partner\stat\export\excel\cells;


use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface ICell
{

    /**
     * @return string - значение в ячейке
    */
    public function value(): string;

    /**
     * @return int - кол-во ячеек для объденения.
    */
    public function merge_count(): int;

    /**
     * Применяет стили указанные в данном классе.
     * @param string $coordinate - координата ячейки/ячеек (B11 или B13:J13)
     * @param Worksheet $sheet - объект, который работает с активным полем таблицы
    */
    public function apply_styles(string $coordinate, Worksheet &$sheet): void;

    /**
     * @return ICell - возвращает объект Cell (который был передан)
    */
    public function cell(): ICell;
}