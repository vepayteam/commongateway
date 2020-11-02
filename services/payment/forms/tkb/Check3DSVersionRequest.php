<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class Check3DSVersionRequest extends Model
{
    public $ExtId;
    public $Amount;
    public $CardInfo;

    public function rules()
    {
        return [
            [['ExtId', 'Amount', 'CardInfo'], 'required'],
        ];
    }

}
