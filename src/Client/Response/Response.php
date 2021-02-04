<?php

namespace Vepay\Gateway\Client\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response implements ResponseInterface
{
    protected PsrResponseInterface $response;

    public function __construct(PsrResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getStatus(): ?string
    {
        return $this->response->getStatusCode();
    }

    public function getContent(): string
    {
        return $this->response->getBody()->getContents();
    }
}