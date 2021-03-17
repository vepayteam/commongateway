<?php


namespace app\services\payment\forms\brs;


use yii\base\Model;

class CheckStatusPayRequest extends Model
{
    public $command = 'c';

    public $trans_id;
    public $client_ip_addr;

    public function rules()
    {
        return [
            [[
                'command', 'trans_id', 'client_ip_addr', 'currency', 'client_ip_addr',
            ], 'required'],
        ];
    }

}
