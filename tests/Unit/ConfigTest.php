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
        $this->assertSame($config, Config::getInstance());
    }

    public function testLogger(): void
    {
        $this->expectException(TypeError::class);

        $config = Config::getInstance();
        $config->logger = new stdClass();
    }
}
