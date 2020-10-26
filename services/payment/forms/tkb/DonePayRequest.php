<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class DonePayRequest extends Model
{
    public $OrderId;
    public $MD;
    public $PaRes;

    public function rules()
    {
        return [
            [['OrderId', 'MD'], 'required'],
            ['OrderId', 'number'],
            [['MD', 'PaRes'], 'string'],
        ];
    }
}