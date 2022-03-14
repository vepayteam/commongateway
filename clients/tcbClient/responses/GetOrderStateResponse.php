<?php

namespace app\clients\tcbClient\responses;

use app\clients\tcbClient\responses\objects\OrderAdditionalInfo;
use app\clients\tcbClient\responses\objects\OrderInfo;
use yii\base\BaseObject;

/**
 * @property-read OrderInfo $orderInfo
 * @property-read OrderAdditionalInfo|null $additionalInfo
 */
class GetOrderStateResponse extends BaseObject
{
    private $_orderInfo;
    private $_additionalInfo;

    public function __construct(OrderInfo $orderInfo, ?OrderAdditionalInfo $additionalInfo)
    {
        parent::__construct();

        $this->_orderInfo = $orderInfo;
        $this->_additionalInfo = $additionalInfo;
    }

    /**
     * @return OrderInfo
     */
    public function getOrderInfo(): OrderInfo
    {
        return $this->_orderInfo;
    }

    /**
     * @return OrderAdditionalInfo|null
     */
    public function getAdditionalInfo(): ?OrderAdditionalInfo
    {
        return $this->_additionalInfo;
    }
}