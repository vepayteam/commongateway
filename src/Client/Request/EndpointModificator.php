<?php

namespace Vepay\Gateway\Client\Request;

trait EndpointModificator
{
    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        $endpoint = $this->endpoint;
        foreach($this->getParameters() as $name => $value) {
            $endpoint = str_replace('{' . $name . '}', $value, $endpoint);
        }

        return $endpoint;
    }
}