<?php

namespace app\helpers;

use Yii;

class Modifiers
{
    public static function searchAndReplacePan(string $input): string
    {
        if (preg_match('/[23456]\d{15,17}/xu', $input, $cards)) {
            foreach ($cards as $card) {
                if (Validators::checkByLuhnAlgorithm($card)) {
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
        $input = Modifiers::searchAndReplacePan($input);
        return Modifiers::searchAndReplaceCredentials($input);
    }
}

