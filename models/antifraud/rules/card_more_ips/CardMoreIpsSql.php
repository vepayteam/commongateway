<?php


namespace app\models\antifraud\rules\card_more_ips;


use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\IpInfo;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFCardIps;
use yii\db\Query;
/**
 * @property IpInfo $ip_info
 * @property string $card_hash
 * */
class CardMoreIpsSql implements ISqlRule
{
    private $card_hash;
    private $ip_info;

    public function __construct($card_hash) {
        $this->card_hash = $card_hash;
        $this->ip_info = new IpInfo();
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
       return AFCardIps::find()
           ->where(['card_hash'=>$this->card_hash])
           ->orderBy('id desc')
           ->limit(100)
           ->with('transaction')
           ;
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
        return $query;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {
        $this->write( $trans_info);
    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {
        $this->write( $trans_info);
    }

    private function write($trans_info){
       $rec = AFCardIps::find()->where(['finger_print_id'=>$trans_info->finger_id()])->one();
       if (!$rec){
           $rec =  new AFCardIps();
           $rec->finger_print_id = $trans_info->finger_id();
           $rec->card_hash = $this->card_hash;
           $rec->ip_num = ip2long($this->ip_info->ip());
       }else{
           $rec->ip_num = ip2long($this->ip_info->ip());
       }
       $rec->save();
    }
}