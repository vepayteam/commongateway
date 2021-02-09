<?php

namespace Vepay\Gateway\Logger;

use Vepay\Gateway\Config;

class LoggerAdapter
{
    private string $logger;

    /**
     * LoggerDecarator constructor.
     * @param string $logger
     */
    public function __construct(string $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    private function getLogLevel(): string
    {
        return Config::getInstance()->logLevel;
    }

    /**
     * @param $message
     * @param $category
     */
    public function trace($message, $category = 'application'): void
    {
        if (in_array($this->getLogLevel(), [LoggerInterface::TRACE_LOG_LEVEL])) {
            $this->logger::trace($category, $message);
        }
    }

    /**
     * @param $message
     * @param $category
     */
    public function info($message, $category = 'application'): void
    {
        if (in_array($this->getLogLevel(),
            [
                LoggerInterface::TRACE_LOG_LEVEL,
                LoggerInterface::INFO_LOG_LEVEL
            ])
        ) {
            $this->logger::info($category, $message);
        }
    }

    /**
     * @param $message
     * @param $category
     */
    public function warning($message, $category = 'application'): void
    {
        if (in_array($this->getLogLevel(),
            [
                LoggerInterface::TRACE_LOG_LEVEL,
                LoggerInterface::INFO_LOG_LEVEL,
                LoggerInterface::WARNING_LOG_LEVEL
            ])
        ) {
            $this->logger::warning($category, $message);
        }
    }

    /**
     * @param $message
     * @param $category
     */
    public function error($message, $category = 'application'): void
    {
        $this->logger::error($category, $message);
    }
}