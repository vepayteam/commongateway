<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

class CheckStatusPayRequest extends BaseRequest
{
    public $orderId;
}
