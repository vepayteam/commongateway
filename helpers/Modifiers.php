<?php

namespace app\helpers;

use Yii;

class Modifiers
{
    public static function searchAndReplacePan(string $input): string
    {
        preg_match_all('/(?<pan>[23456]\d{15,17})/xu', $input, $cards);
        foreach ($cards['pan'] as $card) {
            if (Validators::checkByLuhnAlgorithm($card)) {
                $panMaskedLen = strlen($card) - 10;
                $masked = substr_replace(
                    $card,
                    str_pad('', $panMaskedLen, '*'),
                    6,
                    $panMaskedLen
                );
                $input = str_replace($card, $masked, $input);
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

