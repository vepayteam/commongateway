<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class RefundPayRequest extends Model
{
    public $OrderID;
    public $Amount;

    public function rules()
    {
        return [
            ['OrderID', 'required'],
            [['OrderID', 'Amount'], 'number'],
        ];
    }

}
