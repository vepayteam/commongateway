<?php


namespace app\models\antifraud\rules\black_list_cards;


use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
/**
 * @property TransInfo $trans_info
 * @property bool $as_main
*/
class BlackListCards implements IRule
{

    use DataTrait;

    private $as_main;
    private $trans_info;

    public function __construct($trans_info, $as_main = false) {
        $this->as_main = $as_main;
        $this->trans_info = $trans_info;
    }

    /**
     * Указывает должно ли правило выполняться как основное / или как составное
     * @return boolean true - основное, false - состовное
     */
    public function as_main(): bool
    {
        return $this->as_main;
    }

    /** Получает данные из бд посредством выполнения sql запроса */
    public function data(): array
    {
        return $this->data_trait();
    }

    /**
     * Пустой массив передается в случае когда нужно сделать отдельный sql запрос.
     * @param array $data
     * @return bool
     */
    public function validated(array $data): bool
    {
        if (isset($data[0])){
            return false; //выбраны данные с параметром is_black
        }
        return true;
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        $card_hash = $this->trans_info->card_hash();
        return new BlackListCardsSql($card_hash);
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return 0.001;
    }
}