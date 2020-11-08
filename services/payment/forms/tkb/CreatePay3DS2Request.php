<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class CreatePay3DS2Request extends Model
{
    // TODO: valid rules
    public $ExtId;
    public $CardInfo;
    public $Amount;
    public $AuthenticationData;
    public $CartPositions;
}
