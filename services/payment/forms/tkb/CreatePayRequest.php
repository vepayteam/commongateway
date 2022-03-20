<?php


namespace app\services\payment\forms\tkb;


use app\services\payment\models\PayCard;
use yii\base\Model;

class CreatePayRequest extends Model
{
    /** @var int */
    public $ExtId;
    /** @var int */
    public $Amount;
    /** @var string */
    public $Description;
    /** @var array */
    public $CardInfo;
    /** @var bool */
    public $ShowReturnButton = false;
    /** @var string */
    public $TTL;
    /** @var array */
    public $ClientInfo;

    public function rules()
    {
        return [
            [['OrderID', 'Amount', 'Description', 'CardInfo', 'ShowReturnButton', 'TTL'], 'required'],
        ];
    }

}
