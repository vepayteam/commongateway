<?php

namespace app\models\kkt;

use app\models\extservice\HttpProxy;
use app\models\payonline\Cards;
use qfsx\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

class OrangeData implements IKkm
{
    use HttpProxy;

    private $keyFile;

    /**
     * Отправка POST запроса
     * @param string $post
     * @param string $url
     * @param array $addHeader
     * @param bool $jsonReq
     * @return array [xml, error]
     */
    private function PostReq($post, $url, $addHeader = [])
    {
        //$timout = 50;
        //if (!$jsonReq) {
        $timout = 110;
        //}
        $curl = new Curl();
        Yii::warning("req: url = " . $url . "\r\n" . Cards::MaskCardLog($post), 'rsbcron');
        try {
            $curl->reset()
                ->setOption(CURLOPT_TIMEOUT, $timout)
                ->setOption(CURLOPT_CONNECTTIMEOUT, $timout)
                ->setOption(CURLOPT_HTTPHEADER, array_merge([
                    'Content-type: application/json; charset=utf-8',
                    'X-Signature: ' . $this->Sing($post, $this->keyFile),
                    'TCB-Header-SerializerType: LowerCase'
                ], $addHeader))
                ->setOption(CURLOPT_SSL_VERIFYHOST, false)
                ->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv1')
                //->setOption(CURLOPT_SSLKEY, $this->keyFile)
                //->setOption(CURLOPT_SSLCERT, $this->certFile)
                //->setOption(CURLOPT_CAINFO, $this->caFile)
                ->setOption(CURLOPT_SSL_VERIFYPEER, false)
                ->setOption(CURLOPT_POSTFIELDS, $post)
                ->post($url);
        } catch (\Exception $e) {
            Yii::warning("curlerror: " . $curl->responseCode . ":" . $curl->response, 'rsbcron');
            $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
            return $ans;
        }

        //Yii::warning("Headers: " .print_r($curl->getRequestHeaders(), true), 'merchant');

        $ans = [];
        Yii::warning("curlcode: " . $curl->errorCode, 'rsbcron');
        Yii::warning("curlans: " . $curl->responseCode . ":" . $curl->response, 'rsbcron');
        try {
            switch ($curl->responseCode) {
                case 200:
                case 202:
                    $ans['result'] = Json::decode($curl->response);
                    break;
                case 500:
                    $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
                    $ans['httperror'] = Json::decode($curl->response);
                    break;
                default:
                    $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
                    break;
            }
        } catch (\yii\base\InvalidArgumentException $e) {
            $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
            $ans['httperror'] = $curl->response;
            return $ans;
        }

        return $ans;
    }

}