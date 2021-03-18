<?php


namespace app\services\payment\forms\brs;


use yii\base\Model;

class OutCardPayCheckRequest extends Model
{
    public $target = 'moneytransfer';
    public $operation = 'check';
    public $transfer_type = 'cash2card';
    public $channel = 'B2C';
    public $card;
    public $tr_date;
    public $ccy = '643';
    public $amount;
}
