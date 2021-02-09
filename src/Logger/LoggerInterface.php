<?php

namespace Vepay\Gateway\Logger;

/**
 * Interface LoggerInterface
 * @package Vepay\Gateway\Logger
 */
interface LoggerInterface
{
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