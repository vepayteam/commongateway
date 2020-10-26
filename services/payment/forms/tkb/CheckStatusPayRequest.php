<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class CheckStatusPayRequest extends Model
{
    public $OrderID;

    public function rules()
    {
        return [
            ['OrderID', 'required'],
            ['OrderID', 'number'],
        ];
    }

}