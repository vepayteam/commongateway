<?php


namespace app\models\antifraud\rules\black_list_cards;


use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFCards;
use yii\db\Query;

class BlackListCardsSql implements ISqlRule
{
    private $card_hash;

    public function __construct(string $card_hash) {
        $this->card_hash = $card_hash;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        return AFCards::find()
            ->where(['card_hash'=>$this->card_hash, 'is_black'=>true])
            ->orderBy('id desc')
            ->limit(10);
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
        return $query;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {
        //см. CardMoreOrdersSql
    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {
        //см. CardMoreOrdersSql
    }
}