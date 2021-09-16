<?php


namespace app\services\payment\forms\forta;


use yii\base\Model;

class PaymentRequest extends Model
{
    public $order_id;
    public $amount;
    public $processing_url;
    public $callback_url;
    public $fail_url;
    public $return_url = '';
    public $payer_name = 'NONAME';
    public $payer_phone = '123456789';
    public $payer_email = 'payer@vepay.online';
    public $ttl = 1;
}
