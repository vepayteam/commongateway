<?php

use app\Api\Client\Client;
use app\Api\Client\ClientResponse;

class ApiClientTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var Client
     */
    private $client;

    protected function _before()
    {
        $this->client = new Client();
    }

    protected function _after()
    {
    }

    // tests
    public function testClientInitialization()
    {
        $this->assertIsObject($this->client);
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testClientMethods()
    {
        $this->assertEquals(
            'GET',
            $this->client::METHOD_GET
        );
        $this->assertEquals(
            'POST',
            $this->client::METHOD_POST
        );
        $this->assertEquals(
            'PUT',
            $this->client::METHOD_PUT
        );
    }

    public function testCoreClient()
    {
        $this->assertInstanceOf(\GuzzleHttp\Client::class, $this->client->getClient());
    }

    public function testClientResponse()
    {
        $responseMock = new GuzzleHttp\Psr7\Response(
            200,
            [],
            '{"status":"created","version":"2.0.0","client":{"name":"Peter Parker"}}'
        );
        $response = new ClientResponse($responseMock);
        $this->assertIsArray($response->json());
        $this->assertIsString($response->getBody()->getContents());
        $this->assertEquals('2.0.0', $response->json('version'));
        $this->assertEquals(true, $response->isSuccess());
        $this->assertEquals(false, $response->hasErrors());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
