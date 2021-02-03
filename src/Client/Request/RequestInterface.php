<?php

namespace Vepay\Gateway\Client\Request;

use Vepay\Gateway\Client\Middleware\MiddlewareInterface;

interface RequestInterface
{
    public function setEndpoint(string $endpoint): RequestInterface;

    public function getEndpoint(): string;

    public function setMethod(string $method): RequestInterface;

    public function getMethod(): string;

    public function setHeaders(array $headers): RequestInterface;

    public function addHeader(string $header, string $value): RequestInterface;

    public function getHeaders(): array;

    public function setParameters(array $parameters): RequestInterface;

    public function addParameter(string $name, string $value): RequestInterface;

    public function getParameters(): array;

    public function getPreparedParameters(): array;

    public function setMiddlewares(array $middlewares): RequestInterface;

    public function addMiddleware(MiddlewareInterface $middleware): RequestInterface;

    public function getMiddlewares(): array;

    public function getOptions(): array;

    public function getPreparedOptions(): array;
}