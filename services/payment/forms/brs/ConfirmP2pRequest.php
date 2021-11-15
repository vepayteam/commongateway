<?php


namespace app\services\payment\forms\brs;


use yii\base\Model;

class ConfirmP2pRequest extends Model
{
    public $command = 'c';
    public $trans_id;
    public $client_ip_address;
}
