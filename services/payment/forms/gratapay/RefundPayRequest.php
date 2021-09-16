<?php


namespace app\services\payment\forms\gratapay;


use yii\base\Model;

class RefundPayRequest extends Model
{
    public $amount;
    public $currency;
    public $original_transaction_id;
    public $transaction_id;
}
