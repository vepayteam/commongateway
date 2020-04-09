<?php


namespace app\models\antifraud\tables;


use yii\db\ActiveRecord;
/**
 * @property string $card_hash
 * @property string user_hash;
 * @property bool is_black;
 * @property integer $id;
 * @property integer $finger_print_id;
 * @property AFFingerPrit $transaction;
*/
class AFCards extends ActiveRecord
{
    public static function tableName()
    {
        return 'antifraud_cards';
    }

    public function getTransaction(){
        return $this->hasOne(AFFingerPrit::className(), ['id'=>'finger_print_id']);
    }
}