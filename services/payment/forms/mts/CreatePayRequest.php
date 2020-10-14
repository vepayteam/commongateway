<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

class CreatePayRequest extends Model
{
    public $userName;
    public $password;
    public $orderNumber;
    public $amount;
    public $description;
    public $returnUrl;
    public $sessionTimeoutSecs;

}
