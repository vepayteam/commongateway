<?php

namespace app\services;

use Yii;

class Helpers
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

    public static function searchAndReplacePan(string $input): string
    {
        if (preg_match('/[245]\d{15,17}/xu', $input, $cards)) {
            foreach ($cards as $card) {
                if (Helpers::checkByLuhnAlgorithm($card)) {
                    $panToBeMasked = substr($card, 6, strlen($card) - 10);
                    $masked = str_replace(
                        $panToBeMasked,
                        str_pad('', strlen($panToBeMasked), '*'),
                        $card
                    );
                    return str_replace($card, $masked, $input);
                }
            }
        }
        return $input;
    }

    public static function searchAndReplaceCredentials(string $input): string
    {
        $dbParams = require(Yii::getAlias('@app/config/db.php'));
        $input = str_replace($dbParams['username'], '***', $input);
        return str_replace($dbParams['password'], '***', $input);
    }

    public static function searchAndReplaceSecurity(string $input): string
    {
        $input = Helpers::searchAndReplacePan($input);
        return Helpers::searchAndReplaceCredentials($input);
    }
}

