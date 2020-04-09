<?php


namespace app\models\antifraud\rules\country_card;


use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\tables\AFBinBanks;
use yii\db\Query;
use yii\helpers\VarDumper;

class CountryCardSql implements ISqlRule
{
    private $bin;

    public function __construct(string $bin)
    {
        $this->bin = $bin;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        return AFBinBanks::find()->where(['bin'=>$this->bin])->asArray(true);
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
        return $query
//            ->addSelect('bb.country as country_card')
//            ->innerJoin('antifraud_bin_banks as bb')
//            ->andWhere(['bin' => $this->bin])
            ;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {
        // заглушка.
    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {
        // заглушка
    }

}