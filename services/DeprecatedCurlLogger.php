<?php

namespace app\services;

use app\models\payonline\Cards;
use Yii;
use yii\helpers\Json;

class DeprecatedCurlLogger
{
    public static function handle($curlInfo, string $url, array $headers, $data, $result): void
    {
        Yii::info([
            'Message' => 'Curl request',
            'Info' => Cards::MaskCardLog(is_array($curlInfo) ? Json::encode($curlInfo) : $curlInfo),
            'Url' => $url,
            'Headers' => Json::encode($headers),
            'Data' => Cards::MaskCardLog(is_array($data) ? Json::encode($data) : $data),
            'Ans' => Cards::MaskCardLog(is_array($result) ? Json::encode($result) : $result),
        ], 'merchant');
    }
}