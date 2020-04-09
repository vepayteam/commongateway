<?php


namespace app\models\antifraud\control_objects;

use app\models\antifraud\rules\asn_rule\AsnRule;
use app\models\antifraud\rules\black_list_cards\BlackListCards;
use app\models\antifraud\rules\card_more_ips\CardMoreIps;
use app\models\antifraud\rules\card_more_orders\CardMoreOrders;
use app\models\antifraud\rules\country_card\CountryCard;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\rules\ip_more_cards\IpMoreCards;
use app\models\antifraud\rules\night_pay\NightPay;
use app\models\antifraud\rules\russian_transaction\RussianTransaction;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFFingerPrit;
use app\models\antifraud\tables\AFStat;
use yii\helpers\VarDumper;


/**
 * @property array $data - результат работы sql запросов
 * @property float $weight  - общий "вес" пользователя.
 * @property TransInfo $trans_info
 */
class PaymentValidate implements IAntiFraud
{

    private $data;
    private $weight;
    private $trans_info;

    public function __construct(int $trans_id, string $user_hash, $card_num)
    {
        $this->trans_info = new TransInfo($user_hash, $card_num, $trans_id);
    }


    /**
     * Представляет собой массив объектов которые содержат части скл запроса
     * а также конкретные правила проверки пользователя.
     * работает как фабрика, только генерирует объекты.
     * @return IRule[]
     */
    public function rules(): array
    {
        $trans_info = $this->trans_info;
        return [
            new AsnRule($trans_info, true),
            new NightPay($trans_info, true),
            new CountryCard($trans_info, true),
            new RussianTransaction($trans_info, true),
            new CardMoreOrders($trans_info, true),
            new BlackListCards($trans_info, true),
            new IpMoreCards($trans_info, true),
            new CardMoreIps($trans_info, true)
        ];
    }

    /**
     * Указывает валидно ли правило.
     * @param array $data - данные необходимые для проверки.
     * @return bool
     */
    public function validated(array $data): bool
    {
        $rules = $this->rules();
        $stats = [];
        $weight = 0;
        $trans_info = $this->trans_info;
        foreach ($rules as $rule) {
            $iterate_data = $data;
            if ($rule->as_main()) {
                $iterate_data = $rule->data(); //получаем данные в отдельном sql запросе.
            }
            if ($rule->validated($iterate_data)) {
                $validated = true;
                $rule->sql_obj()->update_success_stat($trans_info);
            } else {
                $weight += $rule->weight();
                $validated = false;
                $rule->sql_obj()->update_failed_stat($trans_info);
            }
            $rule_name = $this->class_basename($rule);
            $result = ["success" => $validated, 'rule_name' => $rule_name, 'weight' => $rule->weight()];
            $stats[] = $result;
        }
        $this->weight = $weight;
        $this->save_stat($stats); //сохраняет результат проверки пользователя
        if ($weight < $this->critical_weight()) {
            return true;
        }
        return false;
    }


    public function as_main(): bool
    {
        return true;
    }


    public function data(): array
    {
        if (is_null($this->data)) {
            $data = $this->sql_obj()->separate_sql()->all();
            if (!$data) {
                $data = [];
            }
            $this->data = $data;
        }
        return $this->data;
    }

    private function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Общая статистика по фильтрам.
     * @param array $stats - [['success' => bool, 'rule_name' => 'string'], ... ]
     */
    private function save_stat(array $stats)
    {
        $finger_id = $this->trans_info->finger_id();
        $total_wight = 0;
        foreach ($stats as $stat) {
            $record = AFStat::find()
                ->where(['rule' => $stat['rule_name']])
                ->andWhere(['finger_print_id' => $finger_id])
                ->one(); //Если статистики по этому правилу, при текущей транзакции не сущетсвует - то создать.
            if (!$record) {
                $record = new AFStat();
            }
            $record->success = $stat['success'];
            $record->rule = $stat['rule_name'];
            $record->finger_print_id = $finger_id;
            $record->current_weight = $stat['weight'];
            $record->save();
            if (!$record->success) {
                $total_wight += $record->current_weight;
            }
        }
        $rec = AFFingerPrit::find()->where(['transaction_id' => $this->trans_info->trans_id(), 'user_hash' => $this->trans_info->user_hash()])->one();
        if ($rec && $rec->weight == 0 && $total_wight !== 0) {
            $rec->weight = $total_wight;
            $rec->save();
        }

    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        $rules = $this->rules();
        $trans_id = $this->trans_info->trans_id();
        $user_hash = $this->trans_info->user_hash();
        return new AntiFraudSql($rules, $trans_id, $user_hash);
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return $this->weight; //
    }

    /** Критический вес при котором транзакция отклоняется*/
    private function critical_weight(): float
    {
        return 1111;
    }

//    private function hash($trans_id)
//    {
//        if (is_null($this->hash)) {
//            /**@var AFFingerPrit $record */
//            $record = AFFingerPrit::find()->where(['transaction_id' => $trans_id])->orderBy('id desc')->one();
//            if ($record) {
//                $this->hash = $record->user_hash;
//            } else {
//                $this->hash = '';
//            }
//        }
//        return $this->hash;
//    }
    public function trans_info()
    {
        return $this->trans_info;
    }
}