<?php


namespace app\models\antifraud\rules\interfaces;


use app\models\antifraud\support_objects\TransInfo;
use yii\db\Query;

interface IRule
{
    public function __construct($transInfo, $as_main = false);

    /**
     * Указывает должно ли правило выполняться как основное / или как составное
     * @return boolean true - основное, false - состовное
     */
    public function as_main(): bool;

    /** Получает данные из бд посредством выполнения sql запроса */
    public function data(): array;

    /**
     * Пустой массив передается в случае когда нужно сделать отдельный sql запрос.
     * @param array $data
     * @return bool
     */
    public function validated(array $data): bool;

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule;

    /** Возвращает "вес" правила*/
    public function weight(): float;

}