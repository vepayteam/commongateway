<?php

namespace Vepay\Gateway\Client\Response;

interface ResponseInterface
{
    public function getStatus(): ?string;

    public function getMessage(): ?string;

    public function getContent(): string;
}