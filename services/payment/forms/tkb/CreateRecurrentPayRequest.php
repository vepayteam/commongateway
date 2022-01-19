<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class CreateRecurrentPayRequest extends Model
{
    public $ExtId;
    public $CardRefID;
    public $Amount;
    public $Description;
}
