<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class TransactionStatusRequest extends BaseObject
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
     * @param int|null $id
     * @param string|null $orderId
     */
    public function __construct(?int $id, ?string $orderId)
    {
        parent::__construct();

        $this->id = $id;
        $this->orderId = $orderId;
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
}