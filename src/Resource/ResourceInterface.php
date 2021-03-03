<?php

namespace Vepay\Gateway\Resource;

use Vepay\Gateway\Client\ClientInterface;

interface ResourceInterface
{
    public function getClient(): ClientInterface;
}