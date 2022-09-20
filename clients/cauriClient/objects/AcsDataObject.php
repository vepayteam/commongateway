<?php

namespace app\clients\cauriClient\objects;

use yii\base\BaseObject;

class AcsDataObject extends BaseObject
{
    /**
     * @var string URL where user must be redirected using HTTP POST with each parameter from parameters object
     */
    private $url;

    /**
     * @var AcsParametersObject
     */
    private $parameters;

    /**
     * @param string $url
     * @param AcsParametersObject $parameters
     */
    public function __construct(string $url, AcsParametersObject $parameters)
    {
        parent::__construct();

        $this->url = $url;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return AcsParametersObject
     */
    public function getParameters(): AcsParametersObject
    {
        return $this->parameters;
    }
}