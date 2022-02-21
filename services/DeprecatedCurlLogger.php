<?php

namespace app\services;

use qfsx\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

class DeprecatedCurlLogger
{
    private $curlInfo;
    private $url;
    private $headers;
    private $data;
    private $result;

    public function __construct($curlInfo, string $url, array $headers, $data, $result)
    {
        $this->curlInfo = $curlInfo;
        $this->url = $url;
        $this->headers = $headers;
        $this->data = $data;
        $this->result = $result;
    }

    public function __invoke()
    {
        Yii::info(
            'Curl request.'
            . PHP_EOL.'Info:'
            . PHP_EOL . (is_array($this->curlInfo) ? Json::encode($this->curlInfo) : $this->curlInfo)
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