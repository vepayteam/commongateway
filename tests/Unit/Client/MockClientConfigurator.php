<?php

namespace Vepay\Gateway\Tests\Unit\Client;

use Vepay\Gateway\Client\AbstractClientConfigurator;

class MockClientConfigurator extends AbstractClientConfigurator
{
    public static function getGatewayName(): string
    {
        return 'testPaymentSystem';
    }
}
