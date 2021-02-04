<?php

namespace Vepay\Gateway\Client\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class Response
 * @package Vepay\Gateway\Client\Response
 */
class Response implements ResponseInterface
{
    protected PsrResponseInterface $response;

    /**
     * Response constructor.
     * @param PsrResponseInterface $response
     */
    public function __construct(PsrResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->response->getBody()->getContents();
    }
}