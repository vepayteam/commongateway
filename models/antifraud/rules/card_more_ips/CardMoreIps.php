<?php


namespace app\models\antifraud\rules\card_more_ips;


use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\IpInfo;
use app\models\antifraud\support_objects\TransInfo;
use app\models\payonline\Cards;
use yii\helpers\VarDumper;
/**
 * @property bool $as_main;
 * @property TransInfo $trans_info;
 * @property IpInfo $ip_info;
*/
class CardMoreIps implements IRule
{
    use DataTrait;

    private $as_main;
    private $trans_info;
    private $ip_info;

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
        if (count($data) < 100){
            return true;
        }
        $asn_array = [];
        foreach ($data as $datum) {
            $ip_str = long2ip($datum['num_ip']);
            $asn_array[] = $this->ip_info->asn_by_ip($ip_str);
        }
        array_unique($asn_array);
        if (count($asn_array) < 10){
            return true;
        }else if(count($asn_array) == 10){
            $asn_array[] = $this->ip_info->asn();
            array_unique($asn_array);
            if (count($asn_array) <= 10){
                return true;
            }
        }
        return false;
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        $card_hash = $this->trans_info->card_hash();
        return new CardMoreIpsSql($card_hash);
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return 0.001;
    }
}