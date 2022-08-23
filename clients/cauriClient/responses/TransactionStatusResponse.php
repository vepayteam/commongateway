<?php

namespace app\clients\cauriClient\responses;

use yii\base\BaseObject;

class TransactionStatusResponse extends BaseObject
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_AUTHENTICATING = 'authenticating';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAIL = 'fail';
    public const STATUS_REVERSED = 'reversed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CHARGEBACK = 'chargeback';

    /**
     * @var int identifier of a transaction
     */
    private $id;

    /**
     * @var string|null merchant's order id of a transaction
     */
    private $orderId;

    /**
     * @var string|null order description
     */
    private $description;

    /**
     * @var string unique and unchangeable merchant's identifier of a user
     */
    private $user;

    /**
     * @var float order price
     */
    private $price;

    /**
     * @var float|null amount earned by merchant
     */
    private $earned;

    /**
     * @var string currency code in the ISO 4217 alfa-3 format
     */
    private $currency;

    /**
     * @var string type of a transaction
     */
    private $type;

    /**
     * @var string status of a transaction
     */
    private $status;

    /**
     * @var bool indicates whether a transaction has error
     */
    private $error;

    /**
     * @var bool indicates whether a transaction is processed in testing mode
     */
    private $sandbox;

    /**
     * @var string|null authorization code of a transaction. Required only for paid payin transactions.
     */
    private $authCode;

    /**
     * @var string|null response code of a transaction. Required only for failed payin transactions.
     */
    private $responseCode;

    /**
     * @var bool indicates whether a transaction can reverse
     */
    private $canReverse;

    /**
     * @var bool indicates whether a transaction can refund
     */
    private $canRefund;

    /**
     * @var bool indicates whether a transaction can partial refund
     */
    private $canPartialRefund;

    /**
     * @param int $id
     * @param string|null $orderId
     * @param string|null $description
     * @param string $user
     * @param float $price
     * @param float|null $earned
     * @param string $currency
     * @param string $type
     * @param string $status
     * @param bool $error
     * @param bool $sandbox
     * @param string|null $authCode
     * @param string|null $responseCode
     * @param bool $canReverse
     * @param bool $canRefund
     * @param bool $canPartialRefund
     */
    public function __construct(
        int $id,
        ?string $orderId,
        ?string $description,
        string $user,
        float $price,
        ?float $earned,
        string $currency,
        string $type,
        string $status,
        bool $error,
        bool $sandbox,
        ?string $authCode,
        ?string $responseCode,
        bool $canReverse,
        bool $canRefund,
        bool $canPartialRefund
    )
    {
        parent::__construct();

        $this->id = $id;
        $this->orderId = $orderId;
        $this->description = $description;
        $this->user = $user;
        $this->price = $price;
        $this->earned = $earned;
        $this->currency = $currency;
        $this->type = $type;
        $this->status = $status;
        $this->error = $error;
        $this->sandbox = $sandbox;
        $this->authCode = $authCode;
        $this->responseCode = $responseCode;
        $this->canReverse = $canReverse;
        $this->canRefund = $canRefund;
        $this->canPartialRefund = $canPartialRefund;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return float|null
     */
    public function getEarned(): ?float
    {
        return $this->earned;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * @return string|null
     */
    public function getAuthCode(): ?string
    {
        return $this->authCode;
    }

    /**
     * @return string|null
     */
    public function getResponseCode(): ?string
    {
        return $this->responseCode;
    }

    /**
     * @return bool
     */
    public function isCanReverse(): bool
    {
        return $this->canReverse;
    }

    /**
     * @return bool
     */
    public function isCanRefund(): bool
    {
        return $this->canRefund;
    }

    /**
     * @return bool
     */
    public function isCanPartialRefund(): bool
    {
        return $this->canPartialRefund;
    }
}