<?php

namespace app\helpers;

use Yii;

class Modifiers
{
    private static $cvvReplaceRegexp = [
        ['/(cvv|csc|cc_cvc)(.+?)\\\"(\d{3,4})\\\"/i', '$1$2\"***\"'],
        ['/(cvv|csc|cc_cvc)(.+?)\"(\d{3,4})\"/i', '$1$2"***"'],
    ];

    public static function searchAndReplaceCvv(string $input): string
    {
        foreach (self::$cvvReplaceRegexp as [$pattern, $replacement]) {
            $input = preg_replace($pattern, $replacement, $input);
        }

        return $input;
    }

    public static function searchAndReplacePan(string $input): string
    {
        preg_match_all('/(?<pan>\b[23456]\d{15,17}\b)/xu', $input, $cards);
        foreach ($cards['pan'] as $card) {
            if (Validators::checkByLuhnAlgorithm($card)) {
                $offset = 6;
                if (strpos($card, '22') === 0) {
                    $offset = 8;
                }
                $panMaskedLen = strlen($card) - $offset - 4;
                $masked = substr_replace(
                    $card,
                    str_pad('', $panMaskedLen, '*'),
                    $offset,
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
        $input = Modifiers::searchAndReplaceCvv($input);
        $input = Modifiers::searchAndReplacePan($input);
        return Modifiers::searchAndReplaceCredentials($input);
    }
}

