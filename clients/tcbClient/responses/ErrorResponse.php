<?php

namespace app\clients\tcbClient\responses;

use yii\base\BaseObject;

/**
 * Ответ с ошибкой.
 *
 * @property-read int $code
 * @property-read string $message
 */
class ErrorResponse extends BaseObject
{
    /** Связаться с банком, выпустившим карту. */
    public const CODE_REFER_TO_CARD_ISSUER = 1;
    /** Связаться с банком, выпустившим карту. Возможны особые условия. */
    public const CODE_REFER_TO_CARD_ISSUER_SPECIAL_CONDITIONS = 2;
    /** Неверно указан мерчант или сервис. */
    public const CODE_INVALID_MERCHANT = 3;
    /** Изъятие карты. */
    public const CODE_PICK_UP_CARD = 4;
    /** Не оплачивается. */
    public const CODE_DO_NOT_HONOR = 5;
    /** Ошибка транзакции. */
    public const CODE_ERROR = 6;

    /**
     * @var int
     */
    private $_code;
    /**
     * @var string
     */
    private $_message;

    /**
     * @param int $code
     * @param string $message
     */
    public function __construct(int $code, string $message)
    {
        parent::__construct();

        $this->_code = $code;
        $this->_message = $message;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->_code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->_message;
    }
}