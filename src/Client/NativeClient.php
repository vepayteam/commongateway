<?php

namespace Vepay\Gateway\Client;

use GuzzleHttp\Client;
use Vepay\Gateway\Client\Request\RequestInterface;
use Vepay\Gateway\Client\Response\ResponseInterface;

class NativeClient implements ClientInterface
{
    protected Client $client;

    public function configure(): void
    {

    }

    public function send(RequestInterface $request): ResponseInterface
    {
        // TODO: Implement send() method.
    }
}