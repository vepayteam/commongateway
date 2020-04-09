<?php


namespace app\models\antifraud\rules\card_more_orders;


use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFCards;
use app\models\antifraud\tables\AFFingerPrit;
use Mpdf\Tag\Q;
use yii\db\Query;
use yii\helpers\VarDumper;

class CardMoreOrdersSql implements ISqlRule
{
    private $card_hash;
    private $user_hash;

    public function __construct(string $card_hash, string $user_hash)
    {
        $this->card_hash = $card_hash;
        $this->user_hash = $user_hash;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        $records = AFCards::find()
            ->where(['card_hash' => $this->card_hash])
            ->orderBy('id desc')
            ->with('transaction')
            ->limit(5)
            ->asArray(true);
        return $records;
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
        return $query
//            ->addSelect('antifraud_cards.card_hash')
//            ->leftJoin('antifraud_cards', '
//            antifraud_cards.id_hash = antifraud_hashes.id and antifraud_hashes.transaction_success = 0')
//            ->orWhere(['antifraud_cards.card_hash'=>$this->card_hash])
            ;
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

    private function write($trans_info)
    {
        $card_hash = $trans_info->card_hash();
        $finger_id = $trans_info->finger_id();
        $query = AFCards::find()->where(['card_hash'=>$card_hash, 'finger_print_id'=>$finger_id]);
        if (!$query->exists()){
            $rec = new AFCards();
            $rec->card_hash = $this->card_hash;
            $rec->finger_print_id = $finger_id;
        }else{
            /**@var AFCards $rec*/
           $rec =  $query->one();
        }
        $is_black = AFCards::find()->where(['card_hash' => $this->card_hash, "is_black" => true])->count();
        if ($is_black > 0) {
            $is_black = true;
        } else {
            $is_black = false;
        }
        $rec->is_black = $is_black;
        $rec->save();
    }
}