<?php

namespace app\services\payment\forms\forta;

use yii\base\Model;

class RecurrentPayRequest extends Model
{
    /**
     * @var string $orderId
     */
    public $orderId;
    /**
     * @var string $cardToken
     */
    public $cardToken;
    /**
     * @var int $amount
     */
    public $amount;
    /**
     * @var string $walletId
     */
    public $walletId;
    /**
     * @var string $callbackUrl
     */
    public $callbackUrl;
    /**
     * @var string $currency
     */
    public $currency = 'RUB';


    public function rules()
    {
        return [
            [['amount', 'cardToken', 'orderId'], 'required'],
            [['cardToken', 'orderId'], 'string'],
            ['amount', 'integer'],
        ];
    }
}
