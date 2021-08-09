<?php

namespace app\modules\hhapi\v1\services\paymentApiService;

class PaymentCreateException extends \Exception
{
    /**
     * Срок оплаты вышел.
     */
    public const INVOICE_EXPIRED = 1;
    /**
     * Шлюз не найден.
     */
    public const NO_GATE = 2;
    /**
     * Ошибка запроса к банку. Легаси.
     */
    public const BANK_ADAPTER_ERROR = 3;
    /**
     * Ошибка оплаты внутри адаптера. Легаси.
     */
    public const CREATE_PAY_ERROR = 4;
    /**
     * Ошибка ТКБ. Легаси.
     */
    public const TKB_ERROR = 5;

    private $legacyMessage;

    /**
     * @param string $message
     * @param int $code
     * @param string|null $legacyMessage Сообщение исключения, выброшенного легаси кодом.
     */
    public function __construct($message = "", $code = 0, ?string $legacyMessage = null)
    {
        parent::__construct($message, $code);

        $this->legacyMessage = $legacyMessage;
    }

    /**
     * Сообщение исключения, выброшенного легаси кодом.
     *
     * @return string|null
     */
    public function getLegacyMessage(): ?string
    {
        return $this->legacyMessage;
    }
}