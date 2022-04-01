<?php

namespace app\services\payment\forms\impaya;

use yii\base\Model;

class CreatePayRequest extends Model
{
    public $_cmd = 'payment_cc';
    public $merchant_id;
    public $hash;
    public $amount;
    public $currency;
    public $invoice;
    public $cl_ip;
    public $description;
    public $cc_name;
    public $cc_num;
    public $cc_expire_m;
    public $cc_expire_y;
    public $cc_cvc;
    public $cl_fname = 'NONAME';
    public $cl_lname = 'NONAME';
    public $cl_email;
    public $cl_country = 'RU';
    public $cl_phone = '79009000000';
    public $browser_data = '';

    public function buildHash($secret)
    {
        $this->hash = md5($this->amount . $this->currency . $this->merchant_id . $secret);
    }
}