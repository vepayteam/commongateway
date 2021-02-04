<?php

namespace Vepay\Gateway;

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

    private function __construct() { }
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
     * @param LoggerInterface $logger
     */
    private function setLogger(LoggerInterface $logger)
    {
        $this->configs['logger'] = $logger;
    }
}