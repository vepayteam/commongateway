<?php


namespace app\models\kkt;


interface IKkm
{
    /**
     * Создание чека
     * @param int $id
     * @param DraftData $data
     * @return array
     */
    public function CreateDraft($id, DraftData $data);

    /**
     * Данные чека
     * @param $id
     * @return array
     */
    public function StatusDraft($id);
}