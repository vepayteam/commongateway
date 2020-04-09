<?php


namespace app\models\antifraud\control_objects;


use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;

interface IAntiFraud
{
    /**проходит по списку всех правил и проверяет каждое*/
    public function validated(array $data): bool;

    /**Правила
     * @return IRule[]
     */
    public function rules(): array;

    /**Содержит логику построения запросов для всех правил*/
    public function sql_obj(): ISqlRule;

    /**данные полученные в результате sql запроса.*/
    public function data();

    /**итоговая информация о транзакции*/
    public function trans_info();
}