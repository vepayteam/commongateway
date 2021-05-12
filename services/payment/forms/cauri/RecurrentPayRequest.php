<?php

namespace app\services\payment\forms\cauri;

use yii\base\Model;

class RecurrentPayRequest extends Model
{
    public $user;
    public $price;
    public $currency = 'RUB';
    public $order_id;
    public $description;
}
