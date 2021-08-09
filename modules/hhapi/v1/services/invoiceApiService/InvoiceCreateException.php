<?php

namespace app\modules\hhapi\v1\services\invoiceApiService;

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