<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class TransactionRefundRequest extends BaseObject
{
    /**
     * @var int|null identifier of a transaction
     */
    private $id;

    /**
     * @var string|null merchant's order id of a transaction
     */
    private $orderId;

    /**
     * @var float|null amount to refund. If you do not pass this value, then a full refund will be made.
     */
    private $amount;

    /**
     * @var string|null string describing return of money reason
     */
    private $comment;

    /**
     * @param int|null $id
     * @param string|null $orderId
     * @param float|null $amount
     * @param string|null $comment
     */
    public function __construct(?int $id, ?string $orderId, ?float $amount, ?string $comment)
    {
        parent::__construct();

        $this->id = $id;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->comment = $comment;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
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
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }
}