<?php

namespace app\services\payment\forms\monetix\models;

use app\services\payment\forms\monetix\BaseModel;
use app\services\payment\models\Currency;
use app\services\payment\models\repositories\CurrencyRepository;

class PaymentModel extends BaseModel
{
    /** @var int */
    public $amount;
    /** @var string */
    public $description;
    /** @var string */
    public $currency;

    /**
     * @param int $amount
     * @param string $description
     * @param string $currency
     */
    public function __construct(int $amount, string $description = '', string $currency = null)
    {
        $this->amount = $amount;
        $this->description = $description;
        if(is_null($currency)) {
            $this->currency = Currency::MAIN_CURRENCY;
        } else {
            $this->currency = $currency;
        }
    }

    public function rules()
    {
        return [
            [['amount', 'currency'], 'required'],
            [['amount'], 'number'],
            [['currency', 'description'], 'string'],
        ];
    }
}