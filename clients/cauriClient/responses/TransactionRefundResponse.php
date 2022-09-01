<?php

namespace app\clients\cauriClient\responses;

use yii\base\BaseObject;

class TransactionRefundResponse extends BaseObject
{
    /**
     * @var int identifier of a money return transaction
     */
    private $id;

    /**
     * @var bool indicates whether return of money was successful
     */
    private $success;

    /**
     * @param int $id
     * @param bool $success
     */
    public function __construct(int $id, bool $success)
    {
        parent::__construct();

        $this->id = $id;
        $this->success = $success;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}