<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

class CheckStatusPayRequest extends Model
{
    public $userName;
    public $password;
    public $orderId;

}
