<?php


namespace app\services\payment\forms\gratapay;


use app\services\payment\models\PaySchet;
use yii\base\Model;

class CreatePayRequest extends Model
{
    public $transaction_id;
    public $amount;
    public $currency;
    public $payment_system;
    public $url = [];

    public $system_fields = [];
    // TODO: real data
    public $three_ds_v2 = [
        'accept_header' => 'text/html,application/xhtml+xml,application/xml',
        'java_enabled' => true,
        'language' => 'ru-RU',
        'color_depth' => 48,
        'screen_height' => 800,
        'screen_width' => 600,
        'time_zone_offset' => "0",
        'user_agent' => 'AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0',
        'ip' => '127.0.0.1',

    ];


    public function getUrls(PaySchet $paySchet)
    {
        return [
            'callback_url' => $paySchet->getOrderdoneUrl(),
            'fail_url' => $paySchet->getOrderdoneUrl(),
            'pending_url' => $paySchet->getOrderdoneUrl(),
            'success_url' => $paySchet->getOrderdoneUrl(),
        ];
    }

}
