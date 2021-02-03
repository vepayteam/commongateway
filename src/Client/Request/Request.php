<?php

namespace Vepay\Gateway\Client\Request;

use Closure;

class Request implements RequestInterface
{
    protected string $endpoint = '';
    protected string $method = '';
    protected array $headers = [];
    protected array $parameters = [];
    protected array $middlewares = [];

    public function setEndpoint(string $endpoint): RequestInterface
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setMethod(string $method): RequestInterface
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setHeaders(array $headers): RequestInterface
    {
        $this->headers = $headers;

        return $this;
    }

    public function addHeader(string $header, string $value): RequestInterface
    {
        $this->headers[$header] = $value;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setParameters(array $parameters): RequestInterface
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function addParameter(string $name, string $value): RequestInterface
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setMiddlewares(array $middlewares): RequestInterface
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    public function addMiddleware(string $name, Closure $closure): RequestInterface
    {
        $this->middlewares[$name] = $closure;

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}