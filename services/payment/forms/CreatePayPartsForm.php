<?php


namespace app\services\payment\forms;


use app\models\traits\ValidateFormTrait;
use app\services\payment\forms\MerchantPayForm;

class CreatePayPartsForm extends MerchantPayForm
{
    use ValidateFormTrait;

    public $parts = [];

    public function rules(): array
    {
        return [
            [['amount'], 'validateAmount'],
            [['extid'], 'string', 'max' => 40],
            [['document_id'], 'string', 'max' => 40],
            [['fullname'], 'string', 'max' => 80],
            [['successurl', 'failurl', 'cancelurl', 'postbackurl'], 'url'],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 300],
            [['descript'], 'string', 'max' => 200],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],

            [['parts'], 'required'],
            [['parts'], 'validateParts'],
            [['type'], 'integer', 'min' => 0],
        ];
    }

    public function validateAmount()
    {
        $amount = 0;
        foreach ($this->parts as $part) {
            if(!is_numeric($part['amount'])) {
                $this->addError('amount', 'Параметр parts/ammount должен быть числом');
                continue;
            }
            $amount += $part['amount'];
        }
        if ($amount < 1 || $amount > 1000000) {
            $this->addError('amount', 'Общая сумма должна быть больше 1 и меньше 1000000');
        }
        $this->setAmount($amount);
    }

    public function validateParts()
    {
        if(!is_array($this->parts)) {
            $this->addError('parts', 'parts должен быть массивом');
        }

        foreach ($this->parts as $part) {
            if(!is_array($part)
                || !array_key_exists('merchant_id', $part)
                || !array_key_exists('amount', $part)
                || !preg_match('/[0-9]+/', $part['amount'])
                || !is_numeric($part['merchant_id'])
                || $part['merchant_id'] < 1
            ) {
                $this->addError('parts', 'Части платежа невалидны');
            }
        }
    }

    private function setAmount($amount)
    {
        $this->amount = $amount;
    }
}
