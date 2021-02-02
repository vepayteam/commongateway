<?php

namespace Vepay\Gateway\Client\Response;

class Response implements ResponseInterface
{
    protected string $raw;

    public function getStatus(): ?string
    {
        return null;
    }

    public function getMessage(): ?string
    {
        return null;
    }

    public function getContent(): string
    {
        return $this->raw;
    }
}