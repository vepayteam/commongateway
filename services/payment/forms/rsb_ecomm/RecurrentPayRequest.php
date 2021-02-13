<?php


namespace app\services\payment\forms\rsb_ecomm;


use yii\base\Model;

class RecurrentPayRequest extends Model
{
    public $command = 'u';

    public $amount;
    public $currency = 643;
    public $client_ip_addr;
    public $cardname;
    public $pan;
    public $expiry;
    public $cvc2;
    public $ecomm_payment_scenario = 3298;

    public function rules()
    {
        return [
            [[
                'command', 'amount', 'currency', 'client_ip_addr',
                'cardname', 'pan', 'expiry', 'cvc2', 'ecomm_payment_scenarion'
            ], 'required'],
            [['amount', 'currency', 'cvc2', 'ecomm_payment_scenarion'], 'number']
        ];
    }

}
