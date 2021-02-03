<?php

namespace Vepay\Gateway\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Vepay\Gateway\Client\Request\RequestInterface;
use Vepay\Gateway\Client\Response\ResponseInterface;

class NativeClient implements ClientInterface
{
    protected Client $client;

    public function configure(array $options): ClientInterface
    {
        $handler = new CurlHandler();
        $options['handler'] = HandlerStack::create($handler);

        $this->client = new Client($options);

        return $this;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        // TODO: Implement send() method.
    }
}