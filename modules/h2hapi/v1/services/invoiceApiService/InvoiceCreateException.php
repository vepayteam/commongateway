<?php

namespace app\modules\h2hapi\v1\services\invoiceApiService;

class InvoiceCreateException extends \Exception
{
    /**
     * Услуга не найдена.
     */
    public const NO_USLUGATOVAR = 1;
    /**
     * Шлюз не найден.
     */
    public const NO_GATE = 2;
}