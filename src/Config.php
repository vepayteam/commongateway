<?php

namespace Vepay\Gateway;

use TypeError;
use Vepay\Gateway\Logger\LoggerAdaptor;
use Vepay\Gateway\Logger\LoggerInterface;

/**
 * Singleton class
 *
 * Class Config
 * @package Vepay\Gateway
 */
class Config
{
    private static ?Config $instance = null;
    private array $configs = [];

    private function __construct()
    {
        $this->logLevel = LoggerInterface::ERROR_LOG_LEVEL;
    }

    private function __clone() { }
    private function __wakeup() { }

    /**
     * @return Config
     */
    public static function getInstance(): Config
    {
        if (static::$instance === null) {
            static::$instance = new Config();
        }

        return static::$instance;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value): void
    {
        (method_exists($this, 'set' . ucfirst($name)))
            ? $this->{'set' . ucfirst($name)}($value)
            : $this->configs[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->configs[$name];
    }

    /**
     * @param string $logger
     */
    private function setLogger(string $logger)
    {
        if (!in_array(LoggerInterface::class, class_implements($logger))) {
            throw new TypeError('The logger class must implement the ' . LoggerInterface::class . ' interface');
        }

        $this->configs['logger'] = new LoggerAdaptor($logger);
    }
}