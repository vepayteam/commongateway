<?php

namespace app\services\payment\forms\impaya;

use yii\base\Model;

class CheckStatusPayRequest extends Model
{
    public $_cmd = 'request';
    public $output = 'json';
    public $merchant_id;
    public $invoice = '';
    public $hash = '';

    public function buildHash($secret)
    {
        $this->hash = md5($this->invoice . $this->merchant_id . $secret);
    }

}