<?php

namespace app\services;

use app\models\payonline\Cards;
use qfsx\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

class CurlLogger
{
    public static function handle(Curl $curl, string $url, array $headers, $data, $result): void
    {
        Yii::info([
            'Message' => 'Curl request',
            'Info' => Json::encode($curl->getInfo()),
            'Url' => $url,
            'Headers' => Json::encode($headers),
            'Data' => Cards::MaskCardLog(is_array($data) ? Json::encode($data) : $data),
            'Ans' => Cards::MaskCardLog(is_array($result) ? Json::encode($result) : $result),
        ], 'merchant');
    }
}