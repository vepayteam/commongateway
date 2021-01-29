<?php

namespace Vepay\Gateway\Client\Response;

interface ResponseInterface
{
    public function setStatus(string $status): ResponseInterface;

    public function getStatus(): ?string;

    public function setMessage(string $message): ResponseInterface;

    public function getMessage(): ?string;

    public function setContent(string $content): ResponseInterface;

    public function getContent(): string;
}