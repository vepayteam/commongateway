<?php


namespace app\models\antifraud\control_objects\refund;


use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * @property IRule[] $rules
*/
class RefundSql implements ISqlRule
{
    private $query;
    private $rules;

    public function __construct(array $rules) {
        $this->rules = $rules;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        if (is_null($this->query)) {
            $query = new Query();
            foreach ($this->rules as $rule) {
                $query = $rule->sql_obj()->compound_sql($query); //генерируем один большой запрос.
            }
            $this->query = $query;
        }
        return $this->query;
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
        return $query;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {
        //пока заглушка.
    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {
        //пока заглушка
    }
}