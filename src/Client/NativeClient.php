<?php

namespace Vepay\Gateway\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Vepay\Gateway\Client\Request\RequestInterface;
use Vepay\Gateway\Client\Response\Response;
use Vepay\Gateway\Client\Response\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class NativeClient
 * @package Vepay\Gateway\Client
 */
class NativeClient implements ClientInterface
{
    protected Client $client;

    /**
     * @param array $options
     * @return ClientInterface
     */
    public function configure(array $options): ClientInterface
    {
        $handler = new CurlHandler();
        $options['handler'] = HandlerStack::create($handler);

        $this->client = new Client($options);

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $this->beforeSend($request);

        $response = $this->client->request(
            $request->getMethod(),
            $request->getEndpoint(),
            array_merge(
                $request->getPreparedParameters(),
                $request->getPreparedOptions(),
                $request->getPreparedHeaders()
            )
        );

        $this->afterSend($request);

        return $this->getAssociatedResponse($request, $response);
    }

    /**
     * @param RequestInterface $request
     */
    private function beforeSend(RequestInterface $request): void
    {
        /** @var HandlerStack $stack */
        $stack = $this->client->getConfig('handler');

        foreach ($request->getMiddlewares() as $middleware) {
            $stack->push($middleware, $middleware->getName());
        }
    }

    /**
     * @param RequestInterface $request
     */
    private function afterSend(RequestInterface $request): void
    {
        /** @var HandlerStack $stack */
        $stack = $this->client->getConfig('handler');

        foreach ($request->getMiddlewares() as $middleware) {
            $stack->remove($middleware);
        }
    }

    /**
     * @param RequestInterface $request
     * @param PsrResponseInterface $response
     * @return ResponseInterface
     */
    private function getAssociatedResponse(RequestInterface $request, PsrResponseInterface $response): ResponseInterface
    {
        $responseClass = str_replace('Request', 'Response', get_class($request));
        if (class_exists($responseClass)) {
            return new $responseClass($response);
        }

        return new Response($response);
    }
}