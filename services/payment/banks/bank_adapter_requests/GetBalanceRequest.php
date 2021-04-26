<?php

namespace app\services\payment\banks\bank_adapter_requests;

use yii\base\Model;

class GetBalanceRequest extends Model
{
    //TODO: check with different $currency ISO format & if bank will respond in all currencies at one time
    /** @var string $currency */
    public $currency = null;
    /** @var array $accounts */
    public $accounts = [];
    /** @var string $bankName */
    public $bankName;
}
