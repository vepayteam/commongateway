<?php


namespace app\services\payment\forms\gratapay;


use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PaySchet;
use Yii;
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

    /**
     * @param PaySchet $paySchet
     * @return array
     */
    public function getUrls(PaySchet $paySchet)
    {
        return [
            'callback_url' => $paySchet->getOrderdoneUrl(),
            'fail_url' => $paySchet->getOrderdoneUrl(),
            'pending_url' => $paySchet->getOrderdoneUrl(),
            'success_url' => $paySchet->getOrderdoneUrl(),
        ];
    }

    /**
     * @param CreatePayForm $createPayForm
     * @return array
     */
    public function getSystemFields(CreatePayForm $createPayForm)
    {
        return [
            'client_id' => $createPayForm->getPaySchet()->ID,
            'card_number' => $createPayForm->CardNumber,
            'card_month' => (int)$createPayForm->CardMonth,
            'card_year' => (int)('20' . $createPayForm->CardYear),
            'cardholder_name' => $createPayForm->CardHolder,
            'card_cvv' => $createPayForm->CardCVC,
            'client_ip' => Yii::$app->request->remoteIP,
            'client_user_agent' => Yii::$app->request->userAgent,
        ];
    }

    /**
     * @return array
     */
    public function getThreeDsV2()
    {
        return [
            'user_agent' => Yii::$app->request->userAgent,
            'ip' => Yii::$app->request->remoteIP,
        ];
    }
}
