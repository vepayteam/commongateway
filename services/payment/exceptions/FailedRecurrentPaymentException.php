<?php

namespace app\services\payment\exceptions;

/**
 * Вызывается если рекуррентный платеж прошел с ошибкой. Стоит присвоить статус Error и остановить опрос платежа
 */
class FailedRecurrentPaymentException extends \Exception
{
    /**
     * @var string|null {@see \app\services\payment\models\PaySchet::$RCCode}
     */
    private $rcCode;
    private $transactionId;

    /**
     * @param string $message
     * @param string|null $rcCode
     */
    public function __construct(string $message = '', ?string $rcCode = null, $transactionId = null)
    {
        parent::__construct($message);

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
