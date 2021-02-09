<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class CreateRecurrentPayRequest extends Model
{
    public $OrderId;
    public $CardRefID;
    public $Amount;
    public $Description;
}
