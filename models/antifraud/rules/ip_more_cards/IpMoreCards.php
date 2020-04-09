<?php


namespace app\models\antifraud\rules\ip_more_cards;

use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\IpInfo;
use app\models\antifraud\support_objects\TransInfo;
use yii\helpers\VarDumper;

/**
 * @property bool $as_main
 * @property TransInfo $trans_info
 * @property IpInfo $ip_info
 */
class IpMoreCards implements IRule
{
    use DataTrait;

    private $as_main;
    private $trans_info;
    private $ip_info;

    public function __construct($trans_info, $as_main = false)
    {
        $this->as_main = $as_main;
        $this->trans_info = $trans_info;
        $this->ip_info = new IpInfo();
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
        if (!$data || count($data) < 5) {
            return true;
        }
        $hash_card = [];
        foreach ($data as $datum) {
            if ($datum['transaction']['status'] == 0) {
                $hash_card[] = $datum['transaction']['card']['card_hash'];
            }
        }
        array_unique($hash_card);
        if ($hash_card < 2) {
            return true;
        }
        return false;
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        $user_hash = $this->trans_info->user_hash();
        $ip = $this->ip_info->ip();
        return new IpMoreCardsSql($ip, $user_hash);
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return 0.001;
    }
}