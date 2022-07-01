<?php

namespace app\clients\yandexPayClient\requests;

use yii\base\BaseObject;

class PaymentUpdateRequest extends BaseObject
{
    /**
     * @var string
     */
    private $messageId;

    /**
     * @var string
     */
    private $eventTime;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $rrn;

    /**
     * @var string
     */
    private $approvalCode;

    /**
     * @var string
     */
    private $eci;

    /**
     * @var string
     */
    private $reasonCode;

    /**
     * @var string
     */
    private $reason;

    /**
     * @param string $messageId
     * @param string $eventTime
     * @param int $amount
     * @param string $currency
     * @param string $status
     * @param string $rrn
     * @param string $approvalCode
     * @param string $eci
     * @param string $reasonCode
     * @param string $reason
     */
    public function __construct(
        string $messageId,
        string $eventTime,
        int $amount,
        string $currency,
        string $status,
        string $rrn,
        string $approvalCode,
        string $eci,
        string $reasonCode,
        string $reason
    )
    {
        parent::__construct();

        $this->messageId = $messageId;
        $this->eventTime = $eventTime;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->status = $status;
        $this->rrn = $rrn;
        $this->approvalCode = $approvalCode;
        $this->eci = $eci;
        $this->reasonCode = $reasonCode;
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getEventTime(): string
    {
        return $this->eventTime;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getRrn(): string
    {
        return $this->rrn;
    }

    /**
     * @return string
     */
    public function getApprovalCode(): string
    {
        return $this->approvalCode;
    }

    /**
     * @return string
     */
    public function getEci(): string
    {
        return $this->eci;
    }

    /**
     * @return string
     */
    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
