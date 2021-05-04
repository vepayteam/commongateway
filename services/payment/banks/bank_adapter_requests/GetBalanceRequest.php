<?php

namespace app\services\payment\banks\bank_adapter_requests;

use yii\base\Model;

class GetBalanceRequest extends Model
{
    //TODO: check with different $currency ISO format & if bank will respond in all currencies at one time
    /** @var string $currency */
    public $currency = "";
    /** @var string $accountNumber */
    public $accountNumber;
    /** @var string $bankName */
    public $bankName = "";
    /** @var $accountType */
    public $accountType;
}
