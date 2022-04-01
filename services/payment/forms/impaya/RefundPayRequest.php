<?php

namespace app\services\payment\forms\impaya;

use yii\base\Model;

class RefundPayRequest extends Model
{
    public $_cmd = 'refund';
    public $merchant_id;
    public $transaction_id;
    public $amount;
    public $hash;

    public function buildHash($secret)
    {
        $this->hash = md5($this->transaction_id . $this->merchant_id . $secret);
    }

}