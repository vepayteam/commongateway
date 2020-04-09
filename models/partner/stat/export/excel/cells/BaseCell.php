<?php


namespace app\models\partner\stat\export\excel\cells;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use yii\helpers\VarDumper;

/**
 * Стили ячеек задаются по аналогии с css в html
 * работает по смыслу фабрики.
*/
class BaseCell implements ICell
{
    private $value;
    private $merge_count;

    /**
     * @param $value - значение ячейки
     * @param $size - кол-во объединенных ячеек начиная с текущей.
     */
    public function __construct(string $value, int $merge_count = 1)
    {
        $this->value = $value;
        $this->merge_count = $merge_count;
    }

    public function value(): string{
        return $this->value;
    }

    public function merge_count(): int{
        return $this->merge_count;
    }

    public function cell(): ICell
    {
        return $this;
    }

    /**
     * Применяет стили указанные в данном классе.
     * @param string $coordinate - координата ячейки/ячеек (B11 или B13:J13)
     * @param Worksheet $sheet - объект, который работает с активным полем таблицы
     */
    public function apply_styles(string $coordinate, Worksheet &$sheet): void
    {
        // ничего не делать. это базовый класс.
    }
}