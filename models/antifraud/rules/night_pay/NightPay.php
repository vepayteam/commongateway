<?php


namespace app\models\antifraud\rules\night_pay;


use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\IpInfo;
use app\models\antifraud\support_objects\TransInfo;
use DateTime;
use DateTimeZone;
use GeoIp2\Database\Reader;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * @property bool $as_main
 * @property IpInfo $ip_info
 */
class NightPay implements IRule
{
    private $as_main;
    private $ip_info;

    public function __construct($trans_info, $as_main = false)
    {
        $this->as_main = $as_main;
        $this->ip_info = new IpInfo();
    }

    /**
     * Пустой массив передается в случае когда нужно сделать отдельный sql запрос.
     * (т.е. правило используется отдельно)
     * @param array $data
     * @return bool
     */
    public function validated(array $data): bool
    {
        $timezone = $this->time_zone();
        $local_hour = $this->local_time($timezone);
        if ($local_hour > 6 && $local_hour < 23){
            return true;
        }else{
            return false;
        }
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
        return [];
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        return new NightPaySql();
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return 0.001;
    }


    private function time_zone()
    {
        try {
            $ip = $this->ip_info->ip();
            return $this->ip_info->time_zone($ip);
        } catch (Exception $e) {
            return 'Europe/Moscow';
        }
    }

    private function local_time(string $time_zone)
    {
        $date = new DateTime('now', new DateTimeZone($time_zone));
        $localtime = $date->format('G');
        return $localtime;
    }
}