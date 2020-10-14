<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

class ConfirmPayRequest extends Model
{
    public $userName;
    public $password;
    public $mdOrder;
    public $paRes;
}
