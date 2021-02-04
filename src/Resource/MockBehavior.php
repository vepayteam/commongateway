<?php

namespace Vepay\Gateway\Resource;

use Vepay\Gateway\Client\Response\ResponseInterface;
use Vepay\Gateway\Tests\Mock\Response\MockResponseInterface;

/**
 * Trait MockBehavior
 * @package Vepay\Gateway\Resource
 */
trait MockBehavior
{
    protected array $operationMapping = [];

    /**
     * @param string $operationName
     * @param MockResponseInterface $response
     */
    public function mock(string $operationName, MockResponseInterface $response): void
    {
        $this->operationMapping[$operationName] = $response;
    }

    /**
     * @param $method
     * @param $arguments
     * @return ResponseInterface
     */
    public function __call($method, $arguments): ResponseInterface
    {
        if (isset($this->operationMapping[$method])) {
            return $this->operationMapping[$method];
        }

        return call_user_func_array([
            $this,
            $method,
        ], $arguments);
    }
}