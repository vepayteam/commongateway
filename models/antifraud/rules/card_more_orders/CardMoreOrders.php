<?php


namespace app\models\antifraud\rules\card_more_orders;


use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
use yii\helpers\VarDumper;

/**
 * @property bool $as_main
 * @property TransInfo $trans_info
 */
class CardMoreOrders implements IRule
{
    use DataTrait;

    private $as_main;
    private $trans_info;

    public function __construct($trans_info, $as_main = false)
    {
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
        $errCnt = 0;
        foreach ($data as $item) {
            if ((bool)$item['transaction']['status']) {
                return true;
            }
            $errCnt++;
        }
        if ($errCnt < 5) {
            return true;
        }
        return false;
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        $card_hash = $this->trans_info->card_hash();
        $user_hash = $this->trans_info->user_hash();
        return new CardMoreOrdersSql($card_hash, $user_hash);
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return 0.001;
    }
}