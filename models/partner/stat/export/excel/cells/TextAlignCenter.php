<?php


namespace app\models\partner\stat\export\excel\cells;


use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use yii\helpers\VarDumper;

class TextAlignCenter implements ICell
{
    private $cell;

    public function __construct(ICell $cell) {
        $this->cell = $cell;
    }

    /**
     * @return string - значение в ячейке
     */
    public function value(): string
    {
        return $this->cell()->value();
    }

    /**
     * @return int - кол-во ячеек для объденения.
     */
    public function merge_count(): int
    {
        return $this->cell()->merge_count();
    }

    /**
     * Применяет стили указанные в данном классе.
     * @param string $coordinate - координата ячейки/ячеек (B11 или B13:J13)
     * @param Worksheet $sheet - объект, который работает с активным полем таблицы
     */
    public function apply_styles(string $coordinate, Worksheet &$sheet): void
    {
        $sheet->getStyle($coordinate)
            ->getAlignment()
            ->setHorizontal('center');
        $this->cell()->apply_styles($coordinate, $sheet);
    }

    /**
     * @return ICell - возвращает объект Cell (который был передан)
     */
    public function cell(): ICell
    {
        return $this->cell;
    }
}