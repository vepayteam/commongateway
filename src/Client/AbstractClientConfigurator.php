<?php

namespace Vepay\Gateway\Client;

use Exception;
use Vepay\Gateway\Config;

abstract class AbstractClientConfigurator
{
    private static array $configuredClients = [];

    abstract public function getGatewayName(): string;

    /**
     * @return ClientInterface
     * @throws Exception
     */
    public function get(): ClientInterface
    {
        if (!Config::getInstance()->{$this->getGatewayName()}) {
            throw new Exception("Config do not found by name '{$this->getGatewayName()}'.");
        }

        if (!isset(self::$configuredClients[$this->getGatewayName()])) {
            self::$configuredClients[$this->getGatewayName()] = new NativeClient();
            self::$configuredClients[$this->getGatewayName()]->configure($this->getGatewayName());
        }

        return self::$configuredClients[$this->getGatewayName()];
    }
}