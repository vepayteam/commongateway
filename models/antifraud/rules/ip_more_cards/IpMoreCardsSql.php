<?php


namespace app\models\antifraud\rules\ip_more_cards;


use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFIps;
use yii\db\Query;
use yii\helpers\VarDumper;

class IpMoreCardsSql implements ISqlRule
{
    private $ip;
    private $hash;

    public function __construct(string $ip, string $user_hash)
    {
        $this->hash = $user_hash;
        $this->ip = $ip;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        $num = $this->ip_num();
        return AFIps::find()
            ->where(['ip_number' => $num])
            ->with('transaction')
            ->orderBy('id desc')
            ->limit(5)
            ->asArray(true);
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

    private function ip_num()
    {
        return ip2long($this->ip);
    }

    private function write($trans_info){
        $finger_id = $trans_info->finger_id();
        $rec = AFIps::find()->where(['finger_print_id'=>$finger_id])->one();
        if (!$rec){
            $rec = new AFIps();
            $rec->finger_print_id = $finger_id;
            $rec->ip_number = (int)$this->ip_num();
            $rec->is_black = false;
            $rec->save();
        }else{
            $rec->ip_number = $this->ip_num();
            $rec->save();
        }
    }
}