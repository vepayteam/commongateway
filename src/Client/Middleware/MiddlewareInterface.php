<?php

namespace Vepay\Gateway\Client\Middleware;

interface MiddlewareInterface
{
    public function getName(): string;
}