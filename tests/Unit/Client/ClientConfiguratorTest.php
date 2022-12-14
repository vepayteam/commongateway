<?php

namespace Vepay\Gateway\Tests\Unit\Client;

use Exception;
use PHPUnit\Framework\TestCase;
use Vepay\Gateway\Client\ClientInterface;
use Vepay\Gateway\Config;

class ClientConfiguratorTest extends TestCase
{
    public function testGetConfig(): void
    {
        Config::getInstance()->{MockClientConfigurator::getGatewayName()} = ['config1' => 'value1', 'config2' => 'value2'];

        $this->assertInstanceOf(ClientInterface::class, MockClientConfigurator::get());
    }
}
