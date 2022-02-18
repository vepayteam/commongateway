<?php

namespace app\services\payment\exceptions;

/**
 * Вызывается при попытке создать платеж уже с существующим extId {@see \app\services\payment\models\PaySchet::$Extid}
 */
class NotUniquePayException extends CreatePayException
{
    const EXCEPTION_MESSAGE = 'Транзакция с передаваемым extid уже существует';

    /**
     * @var int
     */
    private $paySchetId;

    /**
     * @var string
     */
    private $paySchetExtId;

    /**
     * @param int $paySchetId
     * @param string $message
     * @param string $paySchetExtId
     */
    public function __construct(int $paySchetId, string $paySchetExtId, string $message = self::EXCEPTION_MESSAGE)
    {
        parent::__construct($message);

        $this->paySchetId = $paySchetId;
        $this->paySchetExtId = $paySchetExtId;
    }

    public function getPaySchetId(): int
    {
        return $this->paySchetId;
    }

    public function getPaySchetExtId(): string
    {
        return $this->paySchetExtId;
    }
}
