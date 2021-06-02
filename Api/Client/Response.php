<?php

namespace app\Api\Client;

interface Response
{
    public function hasErrors(): bool;
}
