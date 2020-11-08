<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class DonePay3DSv2Request extends Model
{

    public $ExtId;
    public $CardInfo;
    public $Amount;
    public $AuthenticationData;
    public $Description;

}
