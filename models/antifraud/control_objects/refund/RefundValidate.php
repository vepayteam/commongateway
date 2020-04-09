<?php


namespace app\models\antifraud\control_objects\refund;

use app\models\antifraud\control_objects\IAntiFraud;
use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\rules\refund_card\RefundCard;
use app\models\antifraud\support_objects\RefundInfo;
use app\models\antifraud\tables\AFFingerPrit;
use app\models\antifraud\tables\AFStat;
use yii\helpers\VarDumper;

/**
 * валидирует перевод денежных средств на карту/счет физ. лица.
 * @property RefundInfo $ref_info
 */
class RefundValidate implements IAntiFraud
{
    use DataTrait;

    private $ref_info;
    private $weight;
    private $as_main = true;

    public function __construct(RefundInfo $ref_info)
    {
        $this->ref_info = $ref_info;
    }

    /**проходит по списку всех правил и проверяет каждое
     * @param array $data - общие данные полученные из общего запроса separate
     * @return bool
     */
    public function validated(array $data): bool
    {
        $weight = 0;
        $rules = $this->rules();
        $stats = [];
        foreach ($rules as $rule) {
            $iterable_data = $data;
            if ($rule->as_main()) {
                $iterable_data = $rule->data();
            }
            if ($rule->validated($iterable_data)) {
                $validated = true;
            } else {
                $validated = false;
                $weight += $rule->weight();
            }
            $rule_name = $this->class_basename($rule);
            $result = ["success" => $validated, 'rule_name' => $rule_name, 'weight' => $rule->weight()];
            $stats[] = $result;
        }
        $this->weight = $weight;
        $this->save_stat($stats);

        if ($weight >= $this->critical_weight()) {
            return false;
        }
        return true;
    }

    /**Правила
     * @return IRule[]
     */
    public function rules(): array
    {
        $trans_info = $this->trans_info();
        return [
            new RefundCard($trans_info, true)
        ];
    }

    /**Содержит логику построения запросов для всех правил*/
    public function sql_obj(): ISqlRule
    {
        return new RefundSql($this->rules());
    }

    /**данные полученные в результате sql запроса.*/
    public function data()
    {
        return []; //нет собственных данных.
    }

    /**итоговая информация о транзакции*/
    public function trans_info()
    {
        return $this->ref_info;
    }

    public function weight()
    {
        if (is_null($this->weight)) {
            $data = $this->data();
            $this->validated($data);
        }
        return $this->weight;
    }

    private function save_stat(array $stats)
    {
        $finger_id = $this->ref_info->finger_id();
        foreach ($stats as $stat){
            $record_stat = AFStat::find()->where(['finger_print_id' => $finger_id, 'rule'=>$stat['rule_name']])->one();
            if (!$record_stat){
                $record_stat = new AFStat();
            }
            $record_stat->finger_print_id = $finger_id;
            $record_stat->rule = $stat['rule_name'];
            $record_stat->success = $stat['success'];
            $record_stat->current_weight = $stat['weight'];
            $record_stat->save();
        }
        if ($this->weight >= $this->critical_weight()){
            $record = AFFingerPrit::find()->where(['id'=>$finger_id])->one();
            if ($record){
                $record->status = 0;
                $record->weight = $this->weight;
                $record->save();
                $record->refresh();
            }
        }
    }

    public function critical_weight(): float
    {
        return 1.00;
    }

    private function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}