<?php


namespace app\services\payment\forms\forta;


use yii\base\Model;

class CreatePayRequest extends Model
{
    public $cardNumber;
    public $cardHolder;
    public $expireMonth;
    public $expireYear;
    public $cvv;
}
