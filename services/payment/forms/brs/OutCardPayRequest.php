<?php


namespace app\services\payment\forms\brs;


use app\services\payment\models\PartnerBankGate;
use yii\base\Model;

class OutCardPayRequest extends Model implements IXmlRequest
{
    use XmlRequestTrait;

    public $target = 'moneytransfer';
    public $operation = 'pay';
    public $paymentid;
    public $transaction_id;
    public $amount;
    public $ccy = '643';
    public $tr_date;
    public $transfer_type = 'cash2card';
    public $channel = 'B2C';
    public $domestic = 'true';

    /**
     * @return string
     */
    protected function buildBody()
    {
        $xml = '';
        $attributes = $this->attributes;
        $attributes['transaction-id'] = $attributes['transaction_id'];
        unset($attributes['transaction_id']);
        foreach ($attributes as $name => $value) {
            $xmlField = sprintf('<rsb_ns:%1$s>%2$s</rsb_ns:%1$s>', $name, $value);
            $xml .= $xmlField;
        }
        return $xml;
    }
}
