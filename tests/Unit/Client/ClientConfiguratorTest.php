<?php

namespace Vepay\Gateway\Tests\Unit\Client;

use Exception;
use PHPUnit\Framework\TestCase;
use Vepay\Gateway\Client\ClientInterface;
use Vepay\Gateway\Config;

class ClientConfiguratorTest extends TestCase
{
    public function testDoNotFoundConfig(): void
    {
        $this->expectException(Exception::class);

        $configurator = new MockClientConfigurator();
        $configurator->get();
    }

    public function testGetConfig(): void
    {
        $configurator = new MockClientConfigurator();
        Config::getInstance()->{$configurator->getGatewayName()} = ['config1' => 'value1', 'config2' => 'value2'];

        $this->assertInstanceOf(ClientInterface::class, $configurator->get());
    }
}
