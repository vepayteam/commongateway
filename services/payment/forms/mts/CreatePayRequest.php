<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

class CreatePayRequest extends BaseRequest
{
    public $orderNumber;
    public $amount;
    public $description;
    public $returnUrl;
    public $sessionTimeoutSecs;

}
