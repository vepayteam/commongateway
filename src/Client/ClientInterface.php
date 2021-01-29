<?php

namespace Vepay\Gateway\Client;

use Vepay\Gateway\Client\Request\RequestInterface;
use Vepay\Gateway\Client\Response\ResponseInterface;

interface ClientInterface
{
    public function send(RequestInterface $request): ResponseInterface;
}