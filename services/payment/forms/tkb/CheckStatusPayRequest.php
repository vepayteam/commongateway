<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class CheckStatusPayRequest extends Model
{
    public $ExtID;

    public function rules()
    {
        return [
            ['ExtID', 'required'],
            ['ExtID', 'number'],
        ];
    }

}