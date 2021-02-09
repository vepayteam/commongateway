<?php

namespace Vepay\Gateway\Logger\Guzzle;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise as P;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Vepay\Gateway\Client\Middleware\MiddlewareInterface;
use Closure;
use Vepay\Gateway\Config;

class LogMiddleware implements MiddlewareInterface
{
    public function __invoke(callable $handler): Closure
    {
        $logger = Config::getInstance()->logger;
        $formatter = new MessageFormatter('{req_body} - {res_body}');

        return static function (callable $handler) use ($logger, $formatter): callable {
            return static function (RequestInterface $request, array $options = []) use ($handler, $logger, $formatter) {
                return $handler($request, $options)->then(
                    static function ($response) use ($logger, $request, $formatter): ResponseInterface {
                        $message = $formatter->format($request, $response);
                        $logger->info($message, __CLASS__);
                        return $response;
                    },
                    static function ($reason) use ($logger, $request, $formatter): PromiseInterface {
                        $response = $reason instanceof RequestException ? $reason->getResponse() : null;
                        $message = $formatter->format($request, $response, P\Create::exceptionFor($reason));
                        $logger->error($message, __CLASS__);
                        return P\Create::rejectionFor($reason);
                    }
                );
            };
        };
    }

    public function getName(): string
    {
        return 'log';
    }
}