<?php

namespace app\services\payment\forms\cauri;

use yii\base\Model;

class CreatePayRequest extends Model
{
    public $price; // Amount
    public $currency = 'RUB';
    public $user;
    public $description;
    public $acs_return_url;
    public $card;
    public $order_id;
}
