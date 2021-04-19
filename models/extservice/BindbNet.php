<?php

namespace app\models\extservice;

use qfsx\yii2\curl\Curl;
use yii\helpers\Json;

class BindbNet
{
    /**
     * @param $card
     * @return array
     */
    public function GetBankInfo($card)
    {
        $url = "https://lookup.binlist.net/";

        $curl = new Curl();
        $curl->reset();

        $curl
            ->setOption(CURLOPT_SSL_VERIFYHOST, 0)
            ->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv1')
            ->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $curl
            ->setHeader('Accept-Version', 3);

        try {
            $curl->get($url . $card);

            if ($curl->response) {
                $resp = Json::decode($curl->response);
                return ['status' => 1, 'info' => $resp];
            }

        } catch (\Exception $e) {
        }
        return ['status' => 0];

    }
}