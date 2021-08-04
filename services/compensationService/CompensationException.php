<?php


namespace app\services\compensationService;

/**
 * Ошибка в процессе расчета комиссии.
 */
class CompensationException extends \Exception
{

    public const NO_EXCHANGE_RATE = 1;

}