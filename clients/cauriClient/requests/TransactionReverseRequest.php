<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class TransactionReverseRequest extends BaseObject
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
     * @var string|null comment
     */
    private $comment;

    /**
     * @param int|null $id
     * @param string|null $orderId
     * @param string|null $comment
     */
    public function __construct(?int $id, ?string $orderId, ?string $comment)
    {
        parent::__construct();

        $this->id = $id;
        $this->orderId = $orderId;
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
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }
}