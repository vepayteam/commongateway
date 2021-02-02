<?php

namespace Vepay\Gateway\Client\Request;

interface RequestInterface
{
    public function setEndpoint(string $endpoint): RequestInterface;

    public function getEndpoint(): string;

    public function setMethod(string $method): RequestInterface;

    public function getMethod(): string;

    public function setHeaders(array $headers): RequestInterface;

    public function getHeaders(): array;

    public function setParameters(array $parameters): RequestInterface;

    public function getParameters(): array;

    public function setMiddlewares(array $middlewares): RequestInterface;

    public function getMiddlewares(): array;
}