<?php

namespace app\Api\Client;

final class Client extends AbstractClient
{
    public function __construct(
        array $clientConfig = [],
        string $logInfoMessage = ""
    ) {
        parent::__construct($clientConfig, $logInfoMessage);
    }
}
