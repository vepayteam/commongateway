<?php

namespace app\helpers;

/**
 * Содержит функции для в отладки.
 */
class DebugHelper
{
    public static function getStackTrace(): string
    {
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $stack = explode("\n", ob_get_clean());
        array_shift($stack); // убираем эту функцию и стека

        return join("\n", $stack);
    }
}