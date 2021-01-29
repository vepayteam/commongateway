<?php

namespace Vepay\Gateway\Client\Response;

class Response implements ResponseInterface
{
    protected string $raw;
    protected string $status;
    protected string $message;
    protected string $content;

    public function setStatus(string $status): ResponseInterface
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?string
    {
        return null;
    }

    public function setMessage(string $message): ResponseInterface
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return null;
    }

    public function setContent(string $content): ResponseInterface
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): string
    {
        return $this->raw;
    }
}