<?php


namespace app\services\payment\forms\forta;


use yii\base\Model;

class OutCardPayRequest extends Model
{
    public $orderId;
    /** @var array */
    public $cards;
}
