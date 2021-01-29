<?php

namespace Vepay\Gateway;

interface LoggerInterface
{
    public static function trace($message, $category): void;

    public static function error($message, $category): void;

    public static function warning($message, $category): void;

    public static function info($message, $category): void;
}