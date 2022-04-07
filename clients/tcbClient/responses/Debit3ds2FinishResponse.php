<?php

namespace app\clients\tcbClient\responses;

use yii\base\BaseObject;

/**
 * Response for the DebitUnregisteredCard3ds2WofFinish method.
 *
 * @property-read int $orderId Unique order ID from TCB.
 * @property-read string $extId External (client's) payment ID.
 */
class Debit3ds2FinishResponse extends BaseObject
{
    private $_orderId;
    private $_extId;

    public function __construct(int $orderId, string $extId)
    {
        parent::__construct();

        $this->_orderId = $orderId;
        $this->_extId = $extId;
    }

    public function getOrderId(): int
    {
        return $this->_orderId;
    }

    public function getExtId(): string
    {
        return $this->_extId;
    }
}