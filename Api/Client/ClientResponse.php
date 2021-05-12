<?php

namespace app\Api\Client;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Message\ResponseInterface;

final class ClientResponse extends GuzzleResponse
{
    public function __construct(GuzzleResponse $response)
    {
        parent::__construct(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        $code = $this->getStatusCode();
        return (200 <= $code && 300 > $code);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function json(string $key = null)
    {
        $body = $this->getBody();
        $decoded = \GuzzleHttp\json_decode($body, true);
        if (!is_null($key)) {
            $decoded = $decoded[$key];
        }
        return $decoded;
    }

    /**
     * TODO: cache implement
     * @return string
     */
    public function getForCache(): string
    {
        return \GuzzleHttp\json_encode([
            'code' => $this->getStatusCode(),
            'headers' => $this->getHeaders(),
            'body' => (string) $this->getBody(),
            'version' => $this->getProtocolVersion(),
        ]);
    }

    /**
     * TODO: cache implement
     * @param string $cached
     * @return self
     */
    public static function createFromCache(string $cached): self
    {
        $cached = \GuzzleHttp\json_decode($cached, true);

        return new self(new GuzzleResponse(
            $cached['code'],
            $cached['headers'],
            $cached['body'],
            $cached['version']
        ));
    }
}
