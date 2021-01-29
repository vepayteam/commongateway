<?php

namespace Vepay\Gateway\Resource;

use Vepay\Gateway\Client\ClientInterface;

abstract class AbstractResource implements ResourceInterface
{
    private ClientInterface $client;

    public function setClient(ClientInterface $client): ResourceInterface
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ClientInterface
    {
        // TODO: create client with helping ClientConfigurator

        return $this->client;
    }

}