<?php


namespace app\models\antifraud\rules\interfaces;


use app\models\antifraud\support_objects\RefundInfo;
use app\models\antifraud\support_objects\TransInfo;
use yii\db\Query;

interface ISqlRule
{

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query;

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query;

    /**
     * Обновляет статистику правила при удачной транзакции
     * @param TransInfo|RefundInfo $trans_info
     */
    public function update_success_stat($trans_info): void;

    /**
     * Обновляет статистику правила при неудачной транзакции
     *  @param TransInfo|RefundInfo $trans_info
     */
    public function update_failed_stat($trans_info): void;
}