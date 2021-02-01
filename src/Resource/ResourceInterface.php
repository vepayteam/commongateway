<?php

namespace Vepay\Gateway\Resource;

use Vepay\Gateway\Client\ClientConfiguratorInterface;
use Vepay\Gateway\Client\ClientInterface;

interface ResourceInterface
{
    public function setClient(ClientInterface $client);

    public function getClient(ClientConfiguratorInterface $clientConfigurator): ClientInterface;
}