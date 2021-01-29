<?php

namespace Vepay\Gateway\Tests\Unit;

use PHPUnit\Framework\TestCase;
use stdClass;
use Vepay\Gateway\Config;
use TypeError;

class ConfigTest extends TestCase
{
    public function testConfigs(): void
    {
        $config = Config::getInstance();
        $configs = ['config1' => 'value1', 'config2' => 'value2'];
        $config->configPay = $configs;

        $this->assertSame($config, Config::getInstance());
        $this->assertSame($config->configPay, $configs);
    }

    public function testLogger(): void
    {
        $this->expectException(TypeError::class);

        $config = Config::getInstance();
        $config->logger = new stdClass();
    }
}
