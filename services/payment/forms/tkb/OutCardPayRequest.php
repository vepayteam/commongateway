<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class OutCardPayRequest extends Model
{
    public $ExtId;
    public $Amount;
    public $Description;
    public $CardInfo;


}
