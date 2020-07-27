<?php

namespace app\models;

use app\models\extservice\HttpProxy;
use qfsx\yii2\curl\Curl;
use Yii;

class SendHttp
{
    use HttpProxy;

    public $resultCode = 0;
    public $resultText = '';
    public $fullReq = '';

    /**
     * Отправить http запрос Мерчант
     * @param $url
     * @param $SummPay
     * @param $ID
     * @param $ShopIdOrder
     * @param $status
     * @param $key
     * @return bool
     * @throws \Exception
     */
    public function sendShop($url, $SummPay, $ID, $ShopIdOrder, $status, $key)
    {
        return $this->sendCurlGet($url,
            http_build_query([
                'id' => $ShopIdOrder,
                'sum' => str_replace(['.', ',', '_'], '', $SummPay),
                'status' => $status,
                'transact' => $ID,
                'key' => md5($ShopIdOrder.$SummPay.$status.$ID.$key)
            ])
        );
    }

    /**
     * Отправить http запрос МФО
     * @param $url
     * @param $SummPay
     * @param $Extid
     * @param $ID
     * @param $status
     * @param $key
     * @return bool
     * @throws \Exception
     */
    public function sendReq($url, $SummPay, $Extid, $ID, $status, $key)
    {
        return $this->sendCurlGet($url,
            http_build_query([
                'extid' => $Extid,
                'id' => $ID,
                'sum' => str_replace(['.', ',', '_'], '', $SummPay),
                'status' => $status,
                'key' => md5($Extid.$ID.$SummPay.$status.$key)
            ])
        );
    }

    /**
     * Отправить http запрос пользователю
     * @param $url
     * @param $SummPay
     * @param $ID
     * @param $status
     * @param $prov
     * @param $ls
     * @param $extid
     * @param $key
     * @return bool
     * @throws \Exception
     */
    public function sendReqUser($url, $SummPay, $ID, $status, $prov, $ls, $extid, $key)
    {
        return $this->sendCurlGet($url,
            http_build_query([
                'id' => $ID,
                'extid' => $extid,
                'sum' => str_replace(['.', ',', '_'], '', $SummPay),
                'prov' => $prov,
                'ls' => $ls,
                'status' => $status,
                'key' => md5($ID.$extid.$SummPay.$prov.$ls.$status.$key)
            ])
        );
    }

    /**
     * Отправить http запрос get
     * @param $url
     * @param $params
     * @return bool
     * @throws \Exception
     */
    public function sendCurlGet($url, $params)
    {
        $curl = new Curl();
        $curl->reset();
        if (mb_stripos($url, "https://") !== false) {
            $curl
                ->setOption(CURLOPT_TIMEOUT, 120)
                ->setOption(CURLOPT_CONNECTTIMEOUT, 120)
                ->setOption(CURLOPT_SSL_VERIFYHOST, 0)
                ->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv1')
                ->setOption(CURLOPT_SSL_VERIFYPEER, false);
        } elseif (mb_stripos($url, "http://") === false) {
            $url = "http://" . $url;
        }
        $fst = "?";
        if (mb_stripos($url, "?") > 0) {
            $fst = "&";
        }
        if (Yii::$app->params['DEVMODE'] != 'Y' && Yii::$app->params['TESTMODE'] != 'Y' && !empty($this->proxyHost)) {
            $curl->setOption(CURLOPT_PROXY, $this->proxyHost);
            $curl->setOption(CURLOPT_PROXYUSERPWD, $this->proxyUser);
        }
        $curl->get($url . $fst . $params);

        $this->fullReq = $url . $fst . $params;

        Yii::warning("sendCurlGet-url: " . $url . $fst . $params . "\r\n", 'rsbcron');
        if ($curl->errorCode) {
            $this->resultCode = 0;
            $this->resultText = $curl->errorText;
            Yii::warning("sendCurlGet-code: code=" . $curl->errorCode . ": " . $curl->errorText . "\r\n", 'rsbcron');
        } else {
            $this->resultCode = $curl->responseCode;
            $this->resultText = mb_substr(trim(preg_replace("/\s+/", " ", strip_tags($curl->response))), 0, 1024);
            Yii::warning("sendCurlGet-code: http=" . $curl->responseCode . "\r\n", 'rsbcron');
        }
        Yii::warning("sendCurlGet-response: ". mb_substr($curl->response, 0, 250) . "\r\n", 'rsbcron');

        return $curl->errorCode == 0 && $curl->responseCode < 500;
    }

    /**
     * Пересобрать url с параметрами
     * @param $url
     * @param array $params
     * @return string
     */
    public static function UrlBuild($url, array $params)
    {
        $urlArr = parse_url($url);
        $addparams = parse_query($urlArr['query']);
        $params = array_merge($params, $addparams);
        $url = $urlArr['scheme'] . "//";
        if (!empty($urlArr['user'])) {
            $url .= $urlArr['user'] . ":" . $urlArr['pass'] . "@";
        }
        $url .= $urlArr['host'];
        if (!empty($urlArr['port']) && (($urlArr['scheme'] == "http" && $urlArr['port'] != "80") || ($urlArr['scheme'] == "https" && $urlArr['port'] != "443"))) {
            $url .= ":" . $urlArr['port'];
        }
        $url .= "/" . $urlArr['path'] . "?" . http_build_query($params);
        if (!empty($urlArr['fragment'])) {
            $url .= "#" . $urlArr['fragment'];
        }
        return $url;
    }
}
