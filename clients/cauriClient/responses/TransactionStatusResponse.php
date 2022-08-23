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
    private $order_id;

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
    private $auth_code;

    /**
     * @var string|null response code of a transaction. Required only for failed payin transactions.
     */
    private $response_code;

    /**
     * @var bool indicates whether a transaction can reverse
     */
    private $can_reverse;

    /**
     * @var bool indicates whether a transaction can refund
     */
    private $can_refund;

    /**
     * @var bool indicates whether a transaction can partial refund
     */
    private $can_partial_refund;

    /**
     * @param int $id
     * @param string|null $order_id
     * @param string|null $description
     * @param string $user
     * @param float $price
     * @param float|null $earned
     * @param string $currency
     * @param string $type
     * @param string $status
     * @param bool $error
     * @param bool $sandbox
     * @param string|null $auth_code
     * @param string|null $response_code
     * @param bool $can_reverse
     * @param bool $can_refund
     * @param bool $can_partial_refund
     */
    public function __construct(
        int     $id,
        ?string $order_id,
        ?string $description,
        string  $user,
        float   $price,
        ?float  $earned,
        string  $currency,
        string  $type,
        string  $status,
        bool    $error,
        bool    $sandbox,
        ?string $auth_code,
        ?string $response_code,
        bool    $can_reverse,
        bool    $can_refund,
        bool    $can_partial_refund
    )
    {
        parent::__construct();

        $this->id = $id;
        $this->order_id = $order_id;
        $this->description = $description;
        $this->user = $user;
        $this->price = $price;
        $this->earned = $earned;
        $this->currency = $currency;
        $this->type = $type;
        $this->status = $status;
        $this->error = $error;
        $this->sandbox = $sandbox;
        $this->auth_code = $auth_code;
        $this->response_code = $response_code;
        $this->can_reverse = $can_reverse;
        $this->can_refund = $can_refund;
        $this->can_partial_refund = $can_partial_refund;
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
        return $this->order_id;
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
        return $this->auth_code;
    }

    /**
     * @return string|null
     */
    public function getResponseCode(): ?string
    {
        return $this->response_code;
    }

    /**
     * @return bool
     */
    public function isCanReverse(): bool
    {
        return $this->can_reverse;
    }

    /**
     * @return bool
     */
    public function isCanRefund(): bool
    {
        return $this->can_refund;
    }

    /**
     * @return bool
     */
    public function isCanPartialRefund(): bool
    {
        return $this->can_partial_refund;
    }
}