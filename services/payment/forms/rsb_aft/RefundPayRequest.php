<?php


namespace app\services\payment\forms\rsb_aft;


use yii\base\Model;

class RefundPayRequest extends Model
{
    public $command = 'r';
    public $trans_id;

    public function rules()
    {
        return [
            [['command', 'trans_id'], 'required']
        ];
    }

}
