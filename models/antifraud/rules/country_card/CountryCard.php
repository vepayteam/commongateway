<?php


namespace app\models\antifraud\rules\country_card;


use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\IpInfo;
use app\models\antifraud\support_objects\TransInfo;

/**
 * @property bool $as_main
 * @property TransInfo $trans_info
 * @property IpInfo $ip_info
 */
class CountryCard implements IRule
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

    /**
     * Пустой массив передается в случае когда нужно сделать отдельный sql запрос.
     * @param array $data
     * @return bool
     */
    public function validated(array $data): bool
    {
        $country_c = $this->county_by_card($data);
        $country_ip = $this->county_by_ip();
        if (strtolower($country_c) == strtolower($country_ip)) {
            return true;
        }
        return false;
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        $bin_card = $this->trans_info->bin_card();
        return new CountryCardSql($bin_card);
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return 0.001;
    }

    private function county_by_ip(): string
    {
        $iso = $this->ip_info->country_iso();
        switch ($iso) {
            default:
                return $iso;
            case "RU":
                return "RUS";
        }
    }

    private function county_by_card(array $data): string
    {
        if (isset($data[0]['country'])) {
            return $data[0]['country'];
        }
        return '';
    }

    /** Получает данные из бд посредством выполнения sql запроса */
    public function data(): array
    {
        return $this->data_trait();
    }
}