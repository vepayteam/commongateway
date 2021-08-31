<?php

namespace app\services\payment\helpers;

class PaymentHelper
{

    /**
     * Ковертирования суммы в рубли/долары/евро
     */
    public static function convertToFullAmount(int $amount): float
    {
        return round($amount / 100, 2);
    }

    /**
     * Ковертирования суммы в копейки/центы
     */
    public static function convertToPenny(float $amount): int
    {
        return round($amount * 100);
    }

    /**
     * Форматирует сумму в читаемый вид
     * Пример: 10000.00 -> 10 000.00
     */
    public static function formatSum(float $num): string
    {
        return number_format($num, 2, '.', ' ');
    }
}
