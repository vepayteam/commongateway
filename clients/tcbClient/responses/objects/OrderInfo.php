<?php

namespace app\clients\tcbClient\responses\objects;

use Carbon\Carbon;
use yii\base\BaseObject;

/**
 * @property-read string $extId External (client's) payment ID.
 * @property-read int $orderId Unique order ID from TCB.
 * @property-read int $state See {@see OrderInfo::stateList()}.
 * @property-read string $stateDescription
 * @property-read string $type See {@see OrderInfo::typeList()}.
 * @property-read int $amount Amount in fractional currency (cents).
 * @property-read Carbon $datetime Date and time when TCB order created.
 * @property-read Carbon $stateUpdateDateTime
 */
class OrderInfo extends BaseObject
{
    public const STATE_CREDIT_SUCCESS = 0;
    public const STATE_IN_PROCESS = 1;
    public const STATE_HOLD = 2;
    public const STATE_DEBIT_SUCCESS = 3;
    public const STATE_PARTIAL_REFUND = 4;
    public const STATE_FULL_REFUND = 5;
    public const STATE_ERROR = 6;
    public const STATE_CANCEL = 8;

    public const TYPE_FROMREGISTEREDCARD = 'FROMREGISTEREDCARD';
    public const TYPE_FROMUNREGISTEREDCARD = 'FROMUNREGISTEREDCARD';
    public const TYPE_TOREGISTEREDCARD = 'TOREGISTEREDCARD';
    public const TYPE_TOUNREGISTEREDCARD = 'TOUNREGISTEREDCARD';
    public const TYPE_HOLDUNREGISTEREDCARD = 'HOLDUNREGISTEREDCARD';
    public const TYPE_CARDTOCARD = 'CARDTOCARD';
    public const TYPE_DIRECTUNREGISTEREDCARD = 'DIRECTUNREGISTEREDCARD';
    public const TYPE_REFUND = 'REFUND';
    public const TYPE_REVERSE = 'REVERSE';
    public const TYPE_TOACCOUNT = 'TOACCOUNT';
    public const TYPE_PAYMENT = 'PAYMENT';
    /**
     * @var string
     */
    private $_extId;
    /**
     * @var string
     */
    private $_orderId;
    /**
     * @var int
     */
    private $_state;
    /**
     * @var string
     */
    private $_stateDescription;
    /**
     * @var string
     */
    private $_type;
    /**
     * @var int
     */
    private $_amount;
    /**
     * @var Carbon
     */
    private $_datetime;
    /**
     * @var Carbon
     */
    private $_stateUpdateDateTime;

    public function stateList(): array
    {
        return [
            static::STATE_CREDIT_SUCCESS,
            static::STATE_IN_PROCESS,
            static::STATE_HOLD,
            static::STATE_DEBIT_SUCCESS,
            static::STATE_PARTIAL_REFUND,
            static::STATE_FULL_REFUND,
            static::STATE_ERROR,
            static::STATE_CANCEL,
        ];
    }

    public function typeList(): array
    {
        return [
            static::TYPE_FROMREGISTEREDCARD,
            static::TYPE_FROMUNREGISTEREDCARD,
            static::TYPE_TOREGISTEREDCARD,
            static::TYPE_TOUNREGISTEREDCARD,
            static::TYPE_HOLDUNREGISTEREDCARD,
            static::TYPE_CARDTOCARD,
            static::TYPE_DIRECTUNREGISTEREDCARD,
            static::TYPE_REFUND,
            static::TYPE_REVERSE,
            static::TYPE_TOACCOUNT,
            static::TYPE_PAYMENT,
        ];
    }

    /**
     * @param string $extId
     * @param int $orderId
     * @param int $state
     * @param string $stateDescription
     * @param string $type
     * @param int $amount
     * @param Carbon $datetime
     * @param Carbon $stateUpdateDateTime
     */
    public function __construct(
        string $extId,
        int $orderId,
        int $state,
        string $stateDescription,
        string $type,
        int $amount,
        Carbon $datetime,
        Carbon $stateUpdateDateTime
    )
    {
        parent::__construct();

        $this->_extId = $extId;
        $this->_orderId = $orderId;
        $this->_state = $state;
        $this->_stateDescription = $stateDescription;
        $this->_type = $type;
        $this->_amount = $amount;
        $this->_datetime = $datetime;
        $this->_stateUpdateDateTime = $stateUpdateDateTime;
    }

    /**
     * @return string
     */
    public function getExtId(): string
    {
        return $this->_extId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->_orderId;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->_state;
    }

    /**
     * @return string
     */
    public function getStateDescription(): string
    {
        return $this->_stateDescription;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->_type;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->_amount;
    }

    /**
     * @return Carbon
     */
    public function getDatetime(): Carbon
    {
        return $this->_datetime;
    }

    /**
     * @return Carbon
     */
    public function getStateUpdateDateTime(): Carbon
    {
        return $this->_stateUpdateDateTime;
    }
}