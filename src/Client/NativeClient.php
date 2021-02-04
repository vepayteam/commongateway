<?php

namespace Vepay\Gateway\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Vepay\Gateway\Client\Request\RequestInterface;
use Vepay\Gateway\Client\Response\Response;
use Vepay\Gateway\Client\Response\ResponseInterface;

class NativeClient implements ClientInterface
{
    protected Client $client;

    public function configure(array $options): ClientInterface
    {
        $handler = new CurlHandler();
        $options['handler'] = HandlerStack::create($handler);

        $this->client = new Client($options);

        return $this;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        $this->beforeSend($request);

        $response = $this->client->request(
            $request->getMethod(),
            $request->getEndpoint(),
            array_merge($request->getPreparedParameters(), $request->getPreparedOptions())
        );

        $this->afterSend($request);

        // TODO: add request validators

        return new Response($response);
    }

    private function beforeSend(RequestInterface $request): void
    {
        /** @var HandlerStack $stack */
        $stack = $this->client->getConfig('handler');

        foreach ($request->getMiddlewares() as $middleware) {
            $stack->push($middleware, $middleware->getName());
        }
    }

    private function afterSend(RequestInterface $request): void
    {
        /** @var HandlerStack $stack */
        $stack = $this->client->getConfig('handler');

        foreach ($request->getMiddlewares() as $middleware) {
            $stack->remove($middleware);
        }
    }
}