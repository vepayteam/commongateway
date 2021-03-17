<?php


namespace app\services\payment\forms\brs;


use yii\base\Model;

class CreatePayRequest extends Model
{
    public $command = 'i';

    public $mrch_transaction_id;
    public $amount;
    public $currency = 643;
    public $client_ip_addr;
    public $cardname;
    public $pan;
    public $expiry;
    public $cvc2;
    public $server_version = '2.0';

    public function rules()
    {
        return [
            [[
                'command', 'mrch_transaction_id', 'amount', 'currency', 'client_ip_addr',
                'cardname', 'pan', 'expiry', 'cvc2'
            ], 'required'],
            [['amount', 'currency', 'cvc2'], 'number']
        ];
    }

}
