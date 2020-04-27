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

    private $keyName;
    private $keyFile;
    private $inn;

    private $url = "https://apip.orangedata.ru:2443";//https://api.orangedata.ru:12003

    public function __construct()
    {
        $this->keyFile = Yii::$app->basePath . '/config/kassa/private.key';
    }

    /**
     * Создание чека
     * @param int $id
     * @param DraftData $data
     * @return array
     */
    public function CreateDraft($id, DraftData $data)
    {
        $req = Json::encode([
            'id' => $id,
            'inn' => $this->inn,
            'group' => 1,
            'content' => $data->toArray(),
            'key' => $this->keyName,
        ]);
        $ret = $this->HttpReq('POST', '/api/v2/documents/', [], $req);
        if (isset($ret['xml'])) {
            return ['status' => 1];
        }
        return ['status' => 0];
    }

    /**
     * Данные чека
     * @param $id
     * @return array
     */
    public function StatusDraft($id)
    {
        $url = '/api/v2/documents/'.$this->inn.'/status/'.$id;
        $ret = $this->HttpReq('GET', $url);

        if (isset($ret['xml'])) {
            return ['status' => 1, 'data' => $ret['xml']];
        }
        return ['status' => 0];

    }

    /**
     * Отправка запроса
     * @param string $method
     * @param string $url
     * @param array $addHeader
     * @param string $post
     * @return array [xml, error]
     */
    private function HttpReq($method, $url, $addHeader = [], $post = null)
    {
        //$timout = 50;
        //if (!$jsonReq) {
        $timout = 110;
        //}
        $curl = new Curl();
        Yii::warning("req: url = " . $url . "\r\n" . Cards::MaskCardLog($post), 'rsbcron');
        try {
            $curl->reset();
            if (Yii::$app->params['DEVMODE'] != 'Y' && Yii::$app->params['TESTMODE'] != 'Y' && !empty($this->proxyHost)) {
                $curl->setOption(CURLOPT_PROXY, $this->proxyHost);
                $curl->setOption(CURLOPT_PROXYUSERPWD, $this->proxyUser);
            }
            $curl
                ->setOption(CURLOPT_TIMEOUT, $timout)
                ->setOption(CURLOPT_CONNECTTIMEOUT, $timout)
                ->setOption(CURLOPT_SSL_VERIFYHOST, false)
                ->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv1')
                //->setOption(CURLOPT_SSLKEY, $this->keyFile)
                //->setOption(CURLOPT_SSLCERT, $this->certFile)
                //->setOption(CURLOPT_CAINFO, $this->caFile)
                ->setOption(CURLOPT_SSL_VERIFYPEER, false);
            if ($method == 'POST') {
                $curl
                    ->setOption(CURLOPT_HTTPHEADER, array_merge([
                        'Content-type: application/json; charset=utf-8',
                        'X-Signature: ' . $this->Sing($post)
                    ], $addHeader))
                    ->setOption(CURLOPT_POSTFIELDS, $post)
                    ->post($this->url . $url);
            } else {
                $curl->get($this->url . $url);
            }
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

    private function Sing($data)
    {
        $pkeyid = openssl_pkey_get_private($this->keyFile);
        openssl_sign($data, $signature, $pkeyid);
        openssl_free_key($pkeyid);

        return base64_encode($signature);
    }

}