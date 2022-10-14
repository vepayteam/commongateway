<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class Authenticate3DSv2Request extends Model
{
    public $ExtId;
    public $CardInfo;
    public $Amount;
    public $AuthenticateInfo;
    /**
     * @var string 'ECOM' or 'AFT'.
     */
    public $ForceGate;

    public function rules()
    {
        return [
            [['ExtId', 'CardInfo', 'Amount', 'AuthenticateInfo'], 'required'],
        ];

    }

}
