<?php

namespace app\Api\Logger\Http;

use app\Api\Client\Middleware\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise as P;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Closure;

class LogMiddleware implements MiddlewareInterface
{
    private const CATEGORY_REQUEST = 'api_client_http_request';
    private const CATEGORY_INFO = 'api_client_info';
    protected $logInfoMessage;

    public function __construct(string $logInfoMessage)
    {
        $this->logInfoMessage = $logInfoMessage;
    }

    /**
     * @param callable $handler
     * @return Closure
     */
    public function __invoke(callable $handler): Closure
    {
        $logger = \Yii::getLogger();
        $formatter = new MessageFormatter('{method} : {url} : {code} : {req_body} - {res_body}');

        return function (RequestInterface $request, array $options = []) use ($handler, $logger, $formatter) {
            return $handler($request, $options)->then(
                function ($response) use ($logger, $request, $formatter): ResponseInterface {
                    $message = $formatter->format($request, $response);
                    $logger->log($this->getLogInfoMessage(), $logger::LEVEL_WARNING, self::CATEGORY_INFO);
                    $logger->log($message, $logger::LEVEL_WARNING, self::CATEGORY_REQUEST);
                    return $response;
                },
                function ($reason) use ($logger, $request, $formatter): PromiseInterface {
                    if ($reason instanceof RequestException) {
                        $response = $reason->getResponse();
                        $message = $formatter->format($request, $response, P\Create::exceptionFor($reason));
                        $logger->log($this->getLogInfoMessage(), $logger::LEVEL_ERROR, self::CATEGORY_INFO);
                        $logger->log($message, $logger::LEVEL_ERROR, self::CATEGORY_REQUEST);
                    }
                    return P\Create::rejectionFor($reason);
                }
            );
        };
    }

    public function getLogInfoMessage(): string
    {
        return $this->logInfoMessage;
    }

    public function getName(): string
    {
        return 'http_log';
    }
}
