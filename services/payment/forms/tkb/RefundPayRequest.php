<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class RefundPayRequest extends Model
{
    public $ExtId;
    public $amount;
    public $description = 'Отмена заказа';


    public function rules()
    {
        return [
            ['ExtId', 'required'],
            [['ExtId', 'amount'], 'number'],
        ];
    }

}
