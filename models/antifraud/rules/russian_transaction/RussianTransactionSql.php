<?php


namespace app\models\antifraud\rules\russian_transaction;


use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFCountry;
use yii\db\Query;
use yii\helpers\VarDumper;

class RussianTransactionSql implements ISqlRule
{
    private $user_hash;
    private $country;

    public function __construct(string $user_hash, string $country_write) {
        $this->user_hash = $user_hash;
        $this->country = $country_write;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        $req = AFCountry::find()
//            ->select('id as id_history_trans, country as trans_country')
            ->where(['user_hash'=>$this->user_hash])
            ->orderBy('id desc')
            ->limit(2)
            ->asArray(true);
        return $req;
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
         return $query;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {
        $this->write($trans_info);
    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {
        $this->write($trans_info);
    }

    private function write($trans_info){
        $rec = AFCountry::find()->where(['user_hash'=>$trans_info->user_hash(), 'finger_print_id'=>$trans_info->finger_id()])->one();
        if (!$rec){
            $rec = new AFCountry();
            $rec->user_hash = $trans_info->user_hash();
            $rec->country = $this->country;
            $rec->finger_print_id = $trans_info->finger_id();

        }else{
            $rec->country = $this->country;
        }
        $rec->save();
    }
}