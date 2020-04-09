<?php


namespace app\models\antifraud\rules\asn_rule;

use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\IpInfo;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFAsn;
use GeoIp2\Database\Reader;
use Yii;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * Данное правило можно запустить как в составе общей проверки, так и отдельно.
 * @property Reader $reader
 * @property AsnSql $sql_obj;
 * @property IpInfo $ip_info;
 */
class AsnRule implements IRule
{

    use DataTrait;

    private $as_main;
    private $data;
    private $reader;
    private $sql_obj;
    private $ip_info;

    public function __construct($trans_info, $as_main = false) {
        $this->ip_info = new IpInfo();
        $this->as_main = $as_main;
    }


    public function as_main(): bool
    {
        return $this->as_main;
    }

    public function data(): array
    {
       return $this->data_trait();
    }

    /**
     * @param array $data
     * @return bool
     * false:
     * 1. если нет данных для анализа.
     * 2. нет возможности определить ASN (нет ASN в базе)
     * 3. Если ASN в черном списке
     * 4. Если в текущей подсети, каждая 1000 транзакция провалена
     */
    public function validated(array $data): bool
    {
        if (!$data) {
            return false; //значит нет данных об ASN
        } else {
            $data = $data[0]; //может быть много записей.
        }
        if ($data['is_black'] === true) {
            return false;
        }
        $fail_rate = $data['num_fails'] / $data['num_ips'];
        if ($fail_rate > 0.001) {
            return false;
        }
        return true;
    }

    /**
     * Это "вес" правила во всей системе антифрода.
     */
    public function weight(): float
    {
        return 0.001;
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        $asn = $this->ip_info->asn();
        return new AsnSql($asn);
    }
}