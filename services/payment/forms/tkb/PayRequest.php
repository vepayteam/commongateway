<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class PayRequest extends Model
{
    /** @var int */
    public $OrderId;
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
            ['CardInfo', 'validateCardInfo'],
        ];
    }

    public function validateCardInfo($attribute, $params)
    {
        // TODO: добавить проверок
        $value = $this->$attribute;
        if(!array_key_exists('CardNumber', $value)
            || !array_key_exists('CardHolder', $value)
            || !array_key_exists('ExpirationYear', $value)
            || !array_key_exists('ExpirationMonth', $value)
            || !array_key_exists('CVV', $value)
        ) {
            $this->addError($attribute, 'Ошибка данных карты');
        }

    }


}
