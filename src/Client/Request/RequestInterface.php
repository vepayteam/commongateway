<?php

namespace Vepay\Gateway\Client\Request;

interface RequestInterface
{
    public function setEndpoint(string $endpoint): RequestInterface;

    public function setMethod(string $method): RequestInterface;

    public function setHeaders(array $headers): RequestInterface;

    public function setParameters(array $parameters): RequestInterface;

    public function setMiddlewares(array $middlewares): RequestInterface;
}