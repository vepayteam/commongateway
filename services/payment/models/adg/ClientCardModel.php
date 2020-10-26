<?php


namespace app\services\payment\models\adg;


use yii\base\Model;

class ClientCardModel extends Model
{
    public $ccNumber;
    public $cardHolderName;
    public $cvv;
    public $expirationMonth;
    public $expirationYear;

    public function rules()
    {
        return [
            [['ccNumber', 'cardHolderName', 'cvv', 'expirationMonth', 'expirationYear'], 'required'],
            [['ccNumber', 'cardHolderName', 'cvv', 'expirationMonth', 'expirationYear'], 'string'],
        ];
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return trim($this->ccNumber)
        . trim($this->cardHolderName)
        . trim($this->cvv)
        . trim($this->expirationMonth)
        . trim($this->expirationYear);
    }
}
