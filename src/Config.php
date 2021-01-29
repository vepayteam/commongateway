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
    private static Config $instance;
    private array $configs;

    private function __construct()
    {
    }

    public static function getInstance(): Config
    {
        if (!static::$instance) {
            static::$instance = new Config();
        }

        return static::$instance;
    }

    public function __set($name, $value): void
    {
        if (!is_array($value)) {
            return;
        }
        $this->configs[$name] = $value;
    }

    public function __get($name): array
    {
        return $this->configs[$name];
    }
}