<?php

namespace Vepay\Gateway\Client\Response;

class Response implements ResponseInterface
{
    protected array $raw;

    public function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    public function getStatus(): ?string
    {
        return null;
    }

    public function getMessage(): ?string
    {
        return null;
    }

    public function getContent(): array
    {
        return $this->raw;
    }
}