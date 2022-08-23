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
    private $order_id;

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
     * @param string|null $order_id
     * @param float|null $amount
     * @param string|null $comment
     */
    public function __construct(?int $id, ?string $order_id, ?float $amount, ?string $comment)
    {
        parent::__construct();

        $this->id = $id;
        $this->order_id = $order_id;
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
        return $this->order_id;
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