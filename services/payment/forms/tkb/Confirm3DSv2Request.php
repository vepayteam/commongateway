<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class Confirm3DSv2Request extends Model
{
    public $ExtID;
    public $Cres;
    public $Amount;
    public $CardInfo;
    /**
     * @var string 'ECOM' or 'AFT'.
     */
    public $ForceGate;

    public function rules()
    {
        return [
            [['ExtID', 'Cres', 'Amount', 'CardInfo'], 'required'],
        ];
    }

}
