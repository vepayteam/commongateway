<?php

namespace Vepay\Gateway\Client;

use Exception;

/**
 * Class AbstractClientConfigurator
 * @package Vepay\Gateway\Client
 */
abstract class AbstractClientConfigurator
{
    private static array $configuredClients = [];

    abstract public static function getGatewayName(): string;

    abstract public static function getOptions(): array;

    /**
     * @return ClientInterface
     * @throws Exception
     */
    public static function get(): ClientInterface
    {
        $gatewayName = static::getGatewayName();

        if (!isset(self::$configuredClients[$gatewayName])) {
            self::$configuredClients[$gatewayName] =
                (new NativeClient)
                    ->configure(static::getOptions());
        }

        return self::$configuredClients[$gatewayName];
    }
}