<?php

namespace app\services\payment\forms\walletto;

use yii\base\Model;

class CreatePayRequest extends Model
{
    /** @var float */
    public $amount;
    /** @var string */
    public $pan;
    /** @var string */
    public $currency = 'RUB'; // default RUB
    /** @var array */
    public $card;
    /** @var int */
    public $merchant_order_id; // order_id;
    /** @var string */
    public $description;
    /** @var array */
    public $client;
    /** @var array */
    public $location;
    /** @var array */
    public $options;
}
