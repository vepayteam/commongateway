<?php

namespace app\services\payment\forms\monetix;

use app\models\traits\ValidateFormTrait;
use app\services\payment\forms\monetix\models\AcsReturnUrlModel;
use app\services\payment\forms\monetix\models\CardModel;
use app\services\payment\forms\monetix\models\CustomerModel;
use app\services\payment\forms\monetix\models\GeneralModel;
use app\services\payment\forms\monetix\models\PaymentModel;
use app\services\payment\forms\monetix\models\ReturnUrlModel;

class OutCardPayRequest extends BaseModel
{
    use ValidateFormTrait;

    /** @var GeneralModel */
    public $general;
    /** @var CardModel */
    public $card;
    /** @var CustomerModel */
    public $customer;
    /** @var PaymentModel */
    public $payment;

    public function validate($attributeNames = null, $clearErrors = true): bool
    {
        return $this->general->validate()
            && $this->card->validate()
            && $this->customer->validate()
            && $this->payment->validate()
            && parent::validate($attributeNames, $clearErrors);
    }

}