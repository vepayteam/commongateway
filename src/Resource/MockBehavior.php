<?php

namespace Vepay\Gateway\Resource;

use Vepay\Gateway\Client\Response\MockResponseInterface;
use Vepay\Gateway\Client\Response\ResponseInterface;

trait MockBehavior
{
    protected array $operationMapping = [];

    public function mock(string $operationName, MockResponseInterface $response): void
    {
        $this->operationMapping[$operationName] = $response;
    }

    public function __call($name, $arguments): ResponseInterface
    {
        if (isset($this->operationMapping[$name])) {
            return $this->operationMapping[$name];
        }

        return $this->$name($arguments);
    }
}