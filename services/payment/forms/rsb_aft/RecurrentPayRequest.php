<?php


namespace app\services\payment\forms\rsb_aft;


use yii\base\Model;

class RecurrentPayRequest extends Model
{
    public $command = 'e';

    public $amount;
    public $currency = 643;
    public $client_ip_addr;
    public $biller_client_id;
    public $description;
    public $mrch_transaction_id;

    public function rules()
    {
        return [
            [[
                'command', 'amount', 'currency', 'client_ip_addr', 'biller_client_id', 'mrch_transaction_id',
            ], 'required'],
            [['amount', 'currency'], 'number']
        ];
    }

}
