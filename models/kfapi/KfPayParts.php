<?php


namespace app\models\kfapi;


use yii\base\Model;

class KfPayParts extends KfPay
{
    const SCENARIO_FORM = "form";
    const SCENARIO_AUTO = "auto";
    const SCENARIO_STATE = "state";

    const AFTMINSUMM = 1200;

    public $amount = 0;
    public $document_id = '';
    public $fullname = '';
    public $extid = '';
    public $descript = '';
    public $id;
    //public $type = 0;/*'type', */
    public $card = 0;
    public $timeout = 15;
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';

    public $parts = [];

    public function rules()
    {
        return [
            [['amount'], 'validateAmount', 'on' => [self::SCENARIO_FORM, self::SCENARIO_AUTO]],
            [['extid'], 'string', 'max' => 40, 'on' => [self::SCENARIO_FORM, self::SCENARIO_AUTO]],
            [['document_id'], 'string', 'max' => 40, 'on' => [self::SCENARIO_FORM]],
            [['fullname'], 'string', 'max' => 80, 'on' => [self::SCENARIO_FORM]],
            [['successurl', 'failurl', 'cancelurl'], 'url', 'on' => [self::SCENARIO_FORM]],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 300, 'on' => [self::SCENARIO_FORM]],
            [['descript'], 'string', 'max' => 200, 'on' => [self::SCENARIO_FORM]],
            [['card'], 'integer', 'on' => self::SCENARIO_AUTO],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59, 'on' => [self::SCENARIO_FORM]],
            [['card'], 'required', 'on' => self::SCENARIO_AUTO],
            [['id'], 'integer', 'on' => self::SCENARIO_STATE],
            [['id'], 'required', 'on' => self::SCENARIO_STATE],

            [['parts'], 'required', 'on' => [self::SCENARIO_FORM, self::SCENARIO_STATE]],
            [['parts'], 'validateParts', 'on' => [self::SCENARIO_FORM, self::SCENARIO_STATE]],

        ];
    }

    public function validateAmount()
    {
        $amount = 0;
        foreach ($this->parts as $part) {
            $amount += $part['amount'];
        }
        if ($amount < 1 || $amount > 1000000) {
            $this->addError('amount', 'Общая сумма должна быть больше 1 и меньше 1000000');
        }
        $this->setAmount($amount);
    }

    public function validateParts()
    {
        $a = 0;
    }

    private function setAmount($amount)
    {
        $this->amount = $amount;
    }

}
