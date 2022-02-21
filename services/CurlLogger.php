<?php

namespace app\services;

use qfsx\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

class CurlLogger
{
    private $curl;
    private $url;
    private $headers;
    private $data;
    private $result;

    public function __construct(Curl $curl, string $url, array $headers, $data, $result)
    {
        $this->curl = $curl;
        $this->url = $url;
        $this->headers = $headers;
        $this->data = $data;
        $this->result = $result;
    }

    public function __invoke()
    {
        Yii::info(
            'Curl request.'
            . PHP_EOL . 'Info:'
            . PHP_EOL . Json::encode($this->curl->getInfo())
            . PHP_EOL . 'Url:'
            . PHP_EOL . $this->url
            . PHP_EOL . 'Headers:'
            . PHP_EOL . Json::encode($this->headers)
            . PHP_EOL . 'Data:'
            . PHP_EOL . (is_array($this->data) ? Json::encode($this->data) : $this->data)
            . PHP_EOL . 'Ans:'
            . PHP_EOL . (is_array($this->result) ? Json::encode($this->result) : $this->result),
            'merchant'
        );
    }
}