<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class OutCardPayRequest extends Model
{
    public $ExtId;
    public $Amount;
    public $Fullname = 'NONAME NONAME';
    public $Description;
    public $CardInfo;


}
