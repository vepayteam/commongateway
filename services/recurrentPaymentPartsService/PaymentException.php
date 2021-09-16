<?php

namespace app\services\recurrentPaymentPartsService;

/**
 * Ошибка автоплатежа с разбивкой.
 */
class PaymentException extends \Exception
{
    /** Карта просрочена */
    public const CARD_EXPIRED = 1;
    /** Услуга не найдена */
    public const NO_USLUGATOVAR = 2;
    /** Шлюз не найден */
    public const NO_GATE = 3;
    /** Пустая карта */
    public const EMPTY_CARD = 4;
    /** Отсутствует Pan Token */
    public const NO_PAN_TOKEN = 5;
    /** Ошибка запроса к банку */
    public const BANK_EXCEPTION = 6;
}