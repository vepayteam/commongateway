<?php

namespace app\services\payment\helpers;

class PaymentHelper
{

    /**
     * Ковертирования суммы в рубли
     */
    public static function convertToRub(int $penny): float
    {
        return round($penny / 100, 2);
    }

    /**
     * Ковертирования суммы в копейки
     */
    public static function convertToPenny(float $rubles): int
    {
        return round($rubles * 100);
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
