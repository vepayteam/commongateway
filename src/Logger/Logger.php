<?php

namespace Vepay\Gateway\Logger;

use Vepay\Gateway\Logger\Handler\File;
use Vepay\Gateway\Logger\Handler\HandlerInterface;

/**
 * Class Logger
 * @package Vepay\Gateway\Logger
 */
class Logger implements LoggerInterface
{
    /**
     * @param $message
     * @param $category
     */
    public static function trace($message, $category = 'application'): void
    {
        static::log(static::TRACE_LOG_LEVEL, $category, $message);
    }

    /**
     * @param $message
     * @param $category
     */
    public static function error($message, $category = 'application'): void
    {
        static::log(static::ERROR_LOG_LEVEL, $category, $message);
    }

    /**
     * @param $message
     * @param $category
     */
    public static function warning($message, $category = 'application'): void
    {
        static::log(static::WARNING_LOG_LEVEL, $category, $message);
    }

    /**
     * @param $message
     * @param $category
     */
    public static function info($message, $category = 'application'): void
    {
        static::log(static::INFO_LOG_LEVEL, $category, $message);
    }

    /**
     * @param string $level
     * @param string $categoty
     * @param $message
     */
    protected static function log(string $level, string $categoty, $message)
    {
        $handler = static::getHandler();
        $handler->handle($level, $categoty, $message);
    }

    /**
     * @return HandlerInterface
     */
    protected static function getHandler(): HandlerInterface
    {
        return new File('default.log');
    }
}
