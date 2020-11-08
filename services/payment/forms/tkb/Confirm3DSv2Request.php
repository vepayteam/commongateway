<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class Confirm3DSv2Request extends Model
{
    public $ExtID;
    public $Cres;
    public $Amount;
    public $CardInfo;

    public function rules()
    {
        return [
            [['ExtID', 'Cres', 'Amount', 'CardInfo'], 'required'],
        ];
    }

}
