<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class Check3DSVersionRequest extends Model
{
    public $ExtId;
    public $Amount;
    public $CardInfo;
    /**
     * @var string 'ECOM' or 'AFT'.
     */
    public $ForceGate;

    public function rules()
    {
        return [
            [['ExtId', 'Amount', 'CardInfo'], 'required'],
        ];
    }

}
