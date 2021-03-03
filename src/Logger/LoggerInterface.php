<?php

namespace Vepay\Gateway\Logger;

/**
 * Interface LoggerInterface
 * @package Vepay\Gateway\Logger
 */
interface LoggerInterface
{
    const TRACE_LOG_LEVEL = 'trace';
    const ERROR_LOG_LEVEL = 'error';
    const WARNING_LOG_LEVEL = 'warning';
    const INFO_LOG_LEVEL = 'info';

    /**
     * @param $message
     * @param $category
     */
    public static function trace($message, $category): void;

    /**
     * @param $message
     * @param $category
     */
    public static function info($message, $category): void;

    /**
     * @param $message
     * @param $category
     */
    public static function warning($message, $category): void;

    /**
     * @param $message
     * @param $category
     */
    public static function error($message, $category): void;
}