<?php

namespace Vepay\Gateway\Client\Request;

abstract class Request implements RequestInterface
{
    protected string $endpoint = '';
    protected string $method = '';
    protected array $headers = [];
    protected array $parameters = [];
    protected array $options = [];

    public function __construct(array $parameters, array $options = [])
    {
        $this->parameters = $parameters;
        $this->options = $options;
    }

    public function setEndpoint(string $endpoint): RequestInterface
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setMethod(string $method): RequestInterface
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setHeaders(array $headers): RequestInterface
    {
        $this->headers = $headers;

        return $this;
    }

    public function addHeader(string $header, string $value): RequestInterface
    {
        $this->headers[$header] = $value;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setParameters(array $parameters): RequestInterface
    {
        $this->parameters = $parameters;

        return $this;
    }

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

    public function getPreparedOptions(): array
    {
        return ['_options' => $this->getOptions()];
    }


}