<?php

namespace app\helpers;

class Validators
{
    /**
     * Проверка номера карты
     *
     * @param string $s
     * @return bool
     */
    public static function checkByLuhnAlgorithm(string $s): bool
    {
        // оставить только цифры
        $s = strrev(preg_replace('/[^\d]/', '', $s));

        // вычисление контрольной суммы
        $sum = 0;
        for ($i = 0, $j = strlen($s); $i < $j; $i++) {
            // использовать четные цифры как есть
            if (($i % 2) == 0) {
                $val = $s[$i];
            } else {
                // удвоить нечетные цифры и вычесть 9, если они больше 9
                $val = $s[$i] * 2;
                if ($val > 9) $val -= 9;
            }
            $sum += $val;
        }

        // число корректно, если сумма равна 10
        return (($sum % 10) == 0);
    }
}

