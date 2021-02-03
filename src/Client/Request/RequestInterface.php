<?php

namespace Vepay\Gateway\Client\Request;

use Closure;

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

    public function setMiddlewares(array $middlewares): RequestInterface;

    public function addMiddleware(string $name, Closure $closure): RequestInterface;

    public function getMiddlewares(): array;
}