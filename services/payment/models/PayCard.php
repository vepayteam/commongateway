<?php


namespace app\services\payment\models;


use yii\base\Model;

class PayCard extends Model
{
    const CARD_TYPE_REGISTER = 0;
    const CARD_TYPE_NOT_REGISTER = 1;

    /**
     * @var string Токен карты от банка
     * @todo Дать правильное имя переменной.
     */
    public $bankId;
    public $type;
    public $number;
    public $holder;
    public $expYear;
    public $expMonth;
    public $cvv;

    public function rules()
    {
        return [
            [['number', 'expYear', 'expMonth', 'type', 'bankId'], 'number'],
            ['holder', 'string'],
        ];
    }

    public function getError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }


}
