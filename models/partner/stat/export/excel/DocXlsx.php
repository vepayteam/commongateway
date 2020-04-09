<?php


namespace app\models\partner\stat\export\excel;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use yii\helpers\VarDumper;

/**
 * @property Line[] $lines
 * @property Worksheet $sheet
 */
class DocXlsx implements IExportExcel
{

    private $lines;
    private $sheet;
    private $php_obj;
    private $created = false;

    /**
     * @param Line[] $lines - массив строк, который содержит в себе массивыячеек.
     */
    public function __construct(array $lines)
    {
        $this->lines = $lines;
        $this->php_obj = new Spreadsheet();
        $this->php_obj->setActiveSheetIndex(0);
        $this->sheet = $this->php_obj->getActiveSheet();
    }

    public function create()
    {
        $lines = $this->lines;
        $sheet = $this->sheet;
        $index_line = 1;
        foreach ($lines as $line) {
            $cells = $line->cells();
            $index_cell = 0;
            foreach ($cells as $cell) {
                $curr_cell_coordinate = $this->index_to_letter($index_cell) . $index_line;// B2
                if ($cell->merge_count() != 1) {
                    $to_letter = $this->index_to_letter($index_cell + $cell->merge_count() - 1);//B
                    $to_cell_coordinate = $to_letter . $index_line;//B11
                    $coordinate = $curr_cell_coordinate . ":" . $to_cell_coordinate;
                    $sheet->mergeCells($coordinate);
                }else{
                    $coordinate = $curr_cell_coordinate;
                }
                if ($cell->value()) {
                    $sheet->setCellValue($curr_cell_coordinate, $cell->value());
                }
                $cell->apply_styles($coordinate, $sheet);
                if ($cell->merge_count() != 1){
                    $index_cell = $index_cell + $cell->merge_count();
                }else{
                    $index_cell++;
                }
            }
            $index_line++;
        }
        $this->sheet = $sheet;
        $this->created = true;
    }

    /**
     * Этот метод можно расширять. преобразуя spreadsheet
     */
    public function spreadsheet(): Spreadsheet
    {
        if (!$this->created) {
            $this->create();
        }
        return $this->php_obj;
    }

    private function index_to_letter(int $index): string
    {
        $Alpha = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
        if ($index < count($Alpha)) {
            return $Alpha[$index];
        } else {
            $ret = "";
            $ix = $index;
            do {
                $ost = $ix % count($Alpha);
                $ix = $ix / count($Alpha);
                $ret = $Alpha[$ost] . $ret;
            } while ($ix > count($Alpha));
            $ret = $Alpha[$ix - 1] . $ret;
            return $ret;
        }
    }

    /**
     * @return Line[] - список строк (которые можно редактировать)
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * Возвращает документ в формате .xlsx (как строку)
     */
    public function document()
    {
        $tmpfile = \Yii::$app->getBasePath()."\\runtime\\tmp".random_int(10000,100000).".xlsx";
        $writer = IOFactory::createWriter($this->spreadsheet(), 'Xlsx');
        $writer->save($tmpfile);

        $data = file_get_contents($tmpfile);
        unlink($tmpfile);
        return $data;
    }
}