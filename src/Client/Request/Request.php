<?php

namespace Vepay\Gateway\Client\Request;

class Request implements RequestInterface
{
    public string $endpoint;
    public string $method;
    public array $headers;
    public array $parameters;
    public array $middlewares;

    public function setEndpoint(string $endpoint): RequestInterface
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function setMethod(string $method): RequestInterface
    {
        $this->method = $method;

        return $this;
    }

    public function setHeaders(array $headers): RequestInterface
    {
        $this->headers = $headers;

        return $this;
    }

    public function setParameters(array $parameters): RequestInterface
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function setMiddlewares(array $middlewares): RequestInterface
    {
        $this->middlewares = $middlewares;

        return $this;
    }
}