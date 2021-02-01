<?php

namespace Vepay\Gateway\Client;

use GuzzleHttp\Client;
use Vepay\Gateway\Client\Request\RequestInterface;
use Vepay\Gateway\Client\Response\ResponseInterface;

class NativeClient implements ClientInterface
{
    protected Client $client;

    public function configure(): ClientInterface
    {
        // TODO: To config object client.
        return $this;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        // TODO: Implement send() method.
    }
}