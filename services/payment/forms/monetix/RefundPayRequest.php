<?php

namespace app\services\payment\forms\monetix;

class RefundPayRequest extends BaseModel
{
    public $general;
    public $amount;
    public $currency;

}