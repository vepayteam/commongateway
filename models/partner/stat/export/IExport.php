<?php


namespace app\models\partner\stat\export;


interface IExport
{
    /**
     * Производит непосредственно экспорт, отправляет файл пользователю или на email или еще куда либо
    */
    public function content();

    /**
     * Указывает валиден ли экспорт и входные данные.
    */
    public function validated(): bool;

    /**
     * Возвращает обработчик (объект), который трудился над созданием конкретного формата эксопрта.
    */
    public function handler();

    /**
     * Возвращает источник исходных данных по которым строился экспорт.
    */
    public function dataSource(): array;


}