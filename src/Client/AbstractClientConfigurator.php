<?php

namespace Vepay\Gateway\Client;

use Exception;
use Vepay\Gateway\Config;

abstract class AbstractClientConfigurator
{
    private static array $configuredClients = [];

    abstract public static function getGatewayName(): string;

    /**
     * @return ClientInterface
     * @throws Exception
     */
    public static function get(): ClientInterface
    {
        $gatewayName = static::getGatewayName();
        if (!Config::getInstance()->$gatewayName) {
            throw new Exception('Config do not found by name ' . $gatewayName);
        }

        if (!isset(self::$configuredClients[$gatewayName])) {
            self::$configuredClients[$gatewayName] =
                (new NativeClient)
                    ->configure();
        }

        return self::$configuredClients[$gatewayName];
    }
}