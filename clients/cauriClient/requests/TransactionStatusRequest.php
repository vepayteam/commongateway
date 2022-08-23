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
    private $order_id;

    /**
     * @param int|null $id
     * @param string|null $order_id
     */
    public function __construct(?int $id, ?string $order_id)
    {
        parent::__construct();

        $this->id = $id;
        $this->order_id = $order_id;
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
}