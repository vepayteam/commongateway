<?php

namespace Vepay\Gateway\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Vepay\Gateway\Client\Request\RequestInterface;
use Vepay\Gateway\Client\Response\Response;
use Vepay\Gateway\Client\Response\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Vepay\Gateway\Config;
use Vepay\Gateway\Logger\Guzzle\LogMiddleware;

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
        $logMiddleware =  new LogMiddleware;
        $stack = HandlerStack::create($handler);
        $stack->push($logMiddleware, $logMiddleware->getName());

        $options['handler'] = $stack;

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
        $parameters = $request->getPreparedParameters();
        $options = $request->getPreparedOptions();
        $headers = $request->getPreparedHeaders();

        $this->beforeSend($request);

        try {
            $response = $this->client->request(
                $request->getMethod(),
                $request->getEndpoint(),
                array_merge($parameters, $options, $headers)
            );
        } catch (Exception $exception) {
            Config::getInstance()->logger->error($exception->getCode() . ': ' . $exception->getMessage(), __CLASS__);

            throw $exception;
        }
        finally {
            $this->afterSend($request);
        }

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
            $stack->remove($middleware->getName());
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