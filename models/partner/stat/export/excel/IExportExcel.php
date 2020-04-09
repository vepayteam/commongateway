<?php


namespace app\models\partner\stat\export\excel;


use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface IExportExcel
{
    /**
     * @return Line[] - список строк (которые можно редактировать)
    */
    public function lines():array;

    /**
     * @return Spreadsheet - возвращает объект через который можно редактировать таблицу.
    */
    public function spreadsheet(): Spreadsheet;

    /**
     * Возвращает документ в формате .xlsx (как строку)
    */
    public function document();
}