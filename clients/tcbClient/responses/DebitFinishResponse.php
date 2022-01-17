<?php

namespace app\clients\tcbClient\responses;

use yii\base\BaseObject;

/**
 * @property-read int $orderId Уникальный идентификатор операции в платежном шлюзе TKBPay.
 * @property-read string $extId Уникальный идентификатор операции.
 */
class DebitFinishResponse extends BaseObject
{
    /**
     * @var int
     */
    private $_orderId;
    /**
     * @var string
     */
    private $_extId;

    /**
     * @param int $orderId
     * @param string $extId
     */
    public function __construct(int $orderId, string $extId)
    {
        parent::__construct();

        $this->_orderId = $orderId;
        $this->_extId = $extId;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->_orderId;
    }

    /**
     * @return string
     */
    public function getExtId(): string
    {
        return $this->_extId;
    }
}