<?php


namespace app\models\antifraud\control_objects;


use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * @property IRule[] $rules
 * @property Query $query
 * @property string $trans_id
 * @property string $user_hash - хэш пользователя.
 */
class AntiFraudSql implements ISqlRule
{
    private $rules;
    private $query;
    private $trans_id;
    private $user_hash;

    public function __construct(array $rules, string $trans_id, string $user_hash)
    {
        $this->rules = $rules;
        $this->trans_id = $trans_id;
        $this->user_hash = $user_hash;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        if (is_null($this->query)) {
            $query = $this->compound_sql(new Query());
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
        $query->select([
            'antifraud_finger_print.id',
            'antifraud_finger_print.user_hash',
            'antifraud_finger_print.transaction_id',
        ])
            ->from('antifraud_finger_print')
            ->where([
                'antifraud_finger_print.user_hash' => $this->user_hash,
                'antifraud_finger_print.transaction_id' => $this->trans_id
            ]);
        return $query;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {
        foreach ($this->rules as $rule) {
            $rule->sql_obj()->update_success_stat($trans_info);
        }
    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {
        foreach ($this->rules as $rule) {
            $rule->sql_obj()->update_failed_stat($trans_info);
        }
    }
}