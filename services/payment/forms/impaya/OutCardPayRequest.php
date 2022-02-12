<?php

namespace app\services\payment\forms\impaya;

use yii\base\Model;

class OutCardPayRequest extends Model
{
    public $_cmd = 'payout';
    public $merchant_id;
    public $invoice;
    public $amount;
    public $currency;
    public $cc_num;
    public $phone = '';
    public $hash = '';

    public function buildHash($secret)
    {
        $a = 0;
        $this->hash = md5(
            $this->invoice
            . $this->cc_num
            . $this->amount
            . $this->currency
            . $this->merchant_id
            . $secret
        );
    }


}