<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

class ReversePayRequest extends BaseRequest
{
    public $orderId;
}
