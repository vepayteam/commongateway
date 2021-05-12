<?php

namespace app\Api\Client;

final class Client extends AbstractClient
{
    public function __construct(array $clientConfig = [])
    {
        parent::__construct($clientConfig);
    }
}
