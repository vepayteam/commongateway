<?php

namespace Vepay\Gateway\Client\Request;

/**
 * Class Request
 * @package Vepay\Gateway\Client\Request
 */
abstract class Request implements RequestInterface
{
    protected string $endpoint = '';
    protected string $method = '';
    protected array $headers = [];
    protected array $parameters = [];
    protected array $options = [];

    /**
     * Request constructor.
     * @param array $parameters
     * @param array $options
     */
    public function __construct(array $parameters, array $options = [])
    {
        $this->parameters = $parameters;
        $this->options = $options;
    }

    /**
     * @param string $endpoint
     * @return RequestInterface
     */
    public function setEndpoint(string $endpoint): RequestInterface
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $method
     * @return RequestInterface
     */
    public function setMethod(string $method): RequestInterface
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param array $headers
     * @return RequestInterface
     */
    public function setHeaders(array $headers): RequestInterface
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string $header
     * @param string $value
     * @return RequestInterface
     */
    public function addHeader(string $header, string $value): RequestInterface
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array[]
     */
    public function getPreparedHeaders(): array
    {
        return ['headers' => $this->getHeaders()];
    }

    /**
     * @param array $parameters
     * @return RequestInterface
     */
    public function setParameters(array $parameters): RequestInterface
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return RequestInterface
     */
    public function addParameter(string $name, string $value): RequestInterface
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getParameters(): array
    {
        return $this->getParametersValidator()->validate($this->parameters);
    }

    /**
     * @return array|array[]
     * @throws \Exception
     */
    public function getPreparedParameters(): array
    {
        switch (strtoupper($this->getMethod()))
        {
            case 'PUT':
            case 'POST':
                return ['json' => $this->getParameters()];
            case 'GET':
                return ['query' => $this->getParameters()];
            default:
                return [];
        }
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return [];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getOptions(): array
    {
        return $this->getOptionsValidator()->validate($this->options);
    }

    /**
     * @return array[]
     * @throws \Exception
     */
    public function getPreparedOptions(): array
    {
        return ['_options' => $this->getOptions()];
    }
}