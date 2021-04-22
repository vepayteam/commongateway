<?php

namespace app\services\payment\banks\bank_adapter_requests;

use yii\base\Model;

class GetBalanceRequest extends Model
{
    //TODO: check with different $currency ISO format & if bank will respond in all currencies at one time
    /** @var string $account */
    public $currency = null;
    /** @var string $account */
    public $account = null;
}
