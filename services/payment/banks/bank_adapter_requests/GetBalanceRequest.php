<?php

namespace app\services\payment\banks\bank_adapter_requests;

use yii\base\Model;

class GetBalanceRequest extends Model
{
    /**
     * @var string $currency
     */
    public $currency = "RUB"; //by default is RUB

    /**
     * @var string $accountNumber
     */
    public $accountNumber;

    /**
     * @var string $bankName
     */
    public $bankName = "";

    /**
     * @var $accountType
     */
    public $accountType;

    /**
     * @var int
     */
    public $uslugatovarType;
}
