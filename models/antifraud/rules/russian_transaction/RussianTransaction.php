<?php


namespace app\models\antifraud\rules\russian_transaction;

use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\IpInfo;
use app\models\antifraud\support_objects\TransInfo;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use Zxing\Reader;

/**
 * @property TransInfo $trans_info
 * @property bool $as_main
 * @property IpInfo $ip_info
*/
class RussianTransaction implements IRule
{
    use DataTrait;

    private $as_main;
    private $ip_info;
    private $trans_info;

    public function __construct($trans_info, $as_main = false) {
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
        if (!$data) {
            return true;
        }
        $new_data = $data;
        ArrayHelper::multisort($new_data, ['id'],[SORT_DESC]);
        $last_country = strtolower($new_data[0]['country']);
        $current_country = strtolower($this->current_country());
        if (strtolower($last_country) == $current_country){
            return true;
        }
        return false;
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        $user_hash = $this->trans_info->user_hash();
        $country = $this->current_country();
        return new RussianTransactionSql($user_hash, $country);
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return 0.001;
    }

    private function current_country(){
       return $this->ip_info->country_iso();
    }
}