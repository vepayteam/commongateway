<?php

namespace app\services\payment\forms\impaya;

use yii\base\Model;

class CheckStatusPayRequest extends Model
{
    public $_cmd = 'request';
    public $invoice = '';
    public $hash = '';

    public function buildHash($merchantId, $secret)
    {
        $this->hash = md5($this->invoice . $merchantId . $secret);
    }

}