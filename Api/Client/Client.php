<?php

namespace app\Api\Client;

final class Client extends AbstractClient
{
    public function __construct(array $clientConfig = [], $logInfoMessage = '')
    {
        parent::__construct($clientConfig, $logInfoMessage);
    }
}
