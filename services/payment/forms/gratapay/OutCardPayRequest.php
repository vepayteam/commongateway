<?php

namespace app\services\payment\forms\gratapay;

use yii\base\Model;

class OutCardPayRequest extends Model
{
    public $amount;
    public $currency;
    public $payment_system;
    public $transaction_id;
    public $system_fields = [];
}
