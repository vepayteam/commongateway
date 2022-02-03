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
    public $cl_fname;
    public $cl_lname;
    public $cl_email;
    public $cl_country = 'RU';
    public $cl_phone = '79009000000';
    public $browser_data = '{
		"screenColorDepth":24,
		"screenWidth":2195,
		"screenHeight":1235,
		"windowInnerWidth":2195,
		"windowInnerHeight":699,
		"navigatorLanguage":"en-US",
		"timezoneOffset":-180,
		"navigatorJavaEnabled":false,
		"navigatorUserAgent":"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36"
	}';

    public function buildHash($secret)
    {
        $this->hash = md5($this->amount . $this->currency . $this->merchant_id . $secret);
    }
}