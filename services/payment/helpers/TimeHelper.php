<?php

namespace app\services\payment\helpers;

class TimeHelper
{
    /**
     * Секунды в часы с округлением в большую сторону
     *
     * @param int|float $seconds
     * @return int
     */
    public static function secondsToHoursCeil($seconds): int
    {
        return intval(ceil($seconds / 3600));
    }
}
