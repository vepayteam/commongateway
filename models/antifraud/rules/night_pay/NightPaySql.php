<?php


namespace app\models\antifraud\rules\night_pay;


use app\models\antifraud\rules\interfaces\ISqlRule;
use yii\db\Query;


class NightPaySql implements ISqlRule
{

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        return new Query();
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
        return $query;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {
        //заглушка
    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {
        // заглушка
    }
}