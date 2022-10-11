<?php

namespace app\services\payment\exceptions;

/**
 * Вызывается если рекуррентный платеж прошел с ошибкой. Стоит присвоить статус Error и остановить опрос платежа
 */
class FailedRecurrentPaymentException extends \Exception
{
    public const SERVER_ERROR = 1;
    public const CARD_BLOCKED = 2;

    /**
     * @var string|null {@see \app\services\payment\models\PaySchet::$RCCode}
     */
    private $rcCode;
    private $transactionId;

    /**
     * @param string $message
     * @param int $code
     * @param string|null $rcCode
     * @param $transactionId
     */
    public function __construct(
        string $message,
        int $code,
        ?string $rcCode = null,
        $transactionId = null
    )
    {
        parent::__construct($message, $code);

        $this->rcCode = $rcCode;
        $this->transactionId = $transactionId;
    }

    /**
     * @return string|null
     */
    public function getRcCode(): ?string
    {
        return $this->rcCode;
    }

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}
