<?php

namespace app\services\payment\banks\bank_adapter_responses;

use app\services\base\traits\Fillable;
use app\services\payment\models\PaySchet;

class RefundPayResponse extends BaseResponse
{
    const REFUND_TYPE_REFUND = PaySchet::REFUND_TYPE_REFUND;
    const REFUND_TYPE_REVERSE = PaySchet::REFUND_TYPE_REVERSE;

    use Fillable;

    /**
     * @var int тип операции refund {@see PaySchet::REFUND_TYPE_REFUND} или reverse {@see PaySchet::REFUND_TYPE_REVERSE}
     */
    public $refundType = self::REFUND_TYPE_REFUND;

    /**
     * Внутренний идентификатор провайдера, используется для проверки статуса рефанда
     */
    public $extId;

    /**
     * Номер транзакции устанавливается в {@see PaySchet::$ExtBillNumber}
     */
    public $transactionId;

    /**
     * Устанавливается в TKBankAdapter {@see \app\services\payment\banks\TKBankAdapter::refundPay()}
     * далее нигде не используется TODO выпилить?
     */
    public $state;
}
