<?php

namespace Vepay\Gateway\Logger\Handler;

/**
 * Interface HandlerInterface
 * @package Vepay\Gateway\Logger\Handler
 */
interface HandlerInterface
{
    /**
     * @param string $level
     * @param string $category
     * @param $message
     */
    public function handle(string $level, string $category, $message): void;
}
