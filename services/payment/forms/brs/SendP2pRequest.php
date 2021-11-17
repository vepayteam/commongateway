<?php
namespace app\services\payment\forms\brs;


use yii\base\Model;

class SendP2pRequest extends Model
{
    public $command = 'l';
    public $amount;
    public $currency;
    public $client_ip_addr;
    public $cardname;
    public $pan;
    public $expiry;
    public $pan2;
    public $msg_type = 'p2p';
    public $description;
    public $cvc2;
}
