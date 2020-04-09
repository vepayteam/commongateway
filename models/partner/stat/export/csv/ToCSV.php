<?php


namespace app\models\partner\stat\export\csv;


use app\models\partner\stat\PayShetStat;
use Yii;
use yii\helpers\VarDumper;

/**
 * @property array $list - [ строка1 -> ["col1", "col2", ...], строка2 -> ["col1", "col2", ...]]
 * @property string $path - папка, в которую сохранять файл
 * @property string $filename - имя файла (с расширением)
*/
class ToCSV
{
    private $list;
    private $path;
    private $filename;
    private $started_content = false;

    public function __construct(array $list, $path, $filename)
    {
        $this->list = $list;
        $this->path = $path;
        $this->filename = $filename;
        if (!file_exists($this->path)) { //подготовка класса к работе.
            mkdir($this->path);
        }
    }

    /**
     * Производит непосредственно экспорт
     */
    public function export()
    {
        if (!$this->started_content) {
            $this->started_content = true;
            ini_set("auto_detect_line_endings", true); //для веррного отображения в macintosh
            $file = fopen($this->fullpath(), 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); //кодировка UTF-8 BOM
            foreach ($this->list as $data) {
                /**@var array $data*/
                fputcsv($file, $data, ';');
            }
            fclose($file);
        }
    }

    public function fullpath(): string
    {
        return $this->path . $this->filename;
    }

}