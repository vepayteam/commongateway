<?php

namespace app\Api\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Vepay\Gateway\Logger\Guzzle\LogMiddleware;

abstract class AbstractClient
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    /** @var float */
    private const TIMEOUT = 20.0;
    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * AbstractClient constructor.
     * @param array $clientConfig
     */
    public function __construct(array $clientConfig = [])
    {
        $handler = new CurlHandler();
        $logMiddleware =  new LogMiddleware();
        $stack = HandlerStack::create($handler);
        $config = array_merge([
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => self::TIMEOUT,
            'handler' => $stack,
        ], $clientConfig);
        //$stack->push($logMiddleware, $logMiddleware->getName()); //log middleware
        $this->client = new GuzzleClient($config);
//        $this->setClient(new GuzzleClient($config));
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $parameters
     * @param array $headers
     * @return ClientResponse
     * @throws GuzzleException
     */
    final public function request(
        string $method,
        string $endpoint,
        array $parameters = [],
        array $headers = []
    ): ClientResponse {

        $options = $this->getOptions($method, $parameters, $headers);
        $endpoint = $this->prepareEndpoint($endpoint);
        //TODO: cache implement
        //TODO: logger middleware implement
        //$this->beforeSend();
        $response = $this->client->request($method, $endpoint, $options);
        return new ClientResponse($response);
    }

    /**
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @return array[]
     */
    protected function getOptions(
        string $method,
        array $parameters = [],
        array $headers = []
    ): array {
        $options = [
            RequestOptions::FORM_PARAMS => [],
            RequestOptions::QUERY => [],
            RequestOptions::HEADERS => [],
        ];

        if (!empty($parameters) and $method === self::METHOD_POST) {
            $options[RequestOptions::FORM_PARAMS] = $parameters;
        }

        if (!empty($parameters) and in_array($method, [self::METHOD_GET, self::METHOD_PUT])) {
            $options[RequestOptions::QUERY] = $parameters;
        }

        if (!empty($headers)) {
            $options[RequestOptions::HEADERS] = $headers;
        }

        $options[RequestOptions::VERIFY] = false;
        return $options;
    }

    private function beforeSend()
    {
        //TODO: implement middleware logger,
        $stack = $this->client->getConfig('handler');
        $logMiddleware = new LogMiddleware();
        $stack->push($logMiddleware, $logMiddleware->getName());
    }

    /**
     * @param string $endpoint
     * @return string
     */
    final private function prepareEndpoint(string $endpoint): string
    {
        return ltrim($endpoint, '/');
    }

    /**
     * @return GuzzleClient
     */
    public function getClient(): GuzzleClient
    {
        return $this->client;
    }

    /**
     * @param GuzzleClient $client
     */
    public function setClient(GuzzleClient $client): void
    {
        $this->client = $client;
    }
}
