<?php


namespace app\services\payment\models\adg;


use yii\base\Model;

class OrderDataModel extends Model
{

    public $orderId;
    public $orderDescription;
    public $amount;
    public $currencyCode = 'RUB';
    /** @var ClientCardModel */
    public $cc;

    public function rules()
    {
        return [
            [['orderId', 'orderDescription', 'amount', 'currencyCode', 'cc'], 'required'],
            ['cc', 'validateCC'],
        ];
    }

    public function validateCC()
    {
        if(!$this->cc->validate()) {
            $this->addError('cc', 'validateCC error');
        }
    }

    public function getAttributes($names = null, $except = [])
    {
        $result = parent::getAttributes($names, $except);
        $result['cc'] = $this->cc->getAttributes();
        return $result;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return trim ($this->orderId)
        . trim ($this->orderDescription)
        . trim ($this->amount)
        . trim ($this->currencyCode)
        . $this->cc->getSignature();
    }
}
