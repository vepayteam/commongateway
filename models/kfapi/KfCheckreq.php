<?php

namespace app\models\kfapi;

use app\models\payonline\Cards;
use app\services\CurlLogger;
use Yii;
use qfsx\yii2\curl\Curl;
use yii\helpers\Json;

class KfCheckreq
{
    public $message = '';
    protected $result;

    public function CheckReq($url, $extid, $sum)
    {
        try {

            if ($this->sendCurlGet($url, "extid=" . urlencode($extid) . "&sum=" . urlencode($sum/100.0))) {
                $ans = Json::decode($this->result);
                if (isset($ans['success']) && $ans['success'] == true) {
                    return true;
                } else {
                    $this->message = isset($ans['message']) ? $ans['message'] : 'Оплата счета не доступна';
                }
            }
        } catch (\Exception $e) {
            $this->message = "Ошибка проверки возможности оплаты, попробуйте повторить позднее";
        }

        return false;
    }

    /**
     * Отправить http запрос get
     * @param $url
     * @param $params
     * @return bool
     * @throws \Exception
     */
    protected function sendCurlGet($url, $params)
    {
        $curl = new Curl();
        $curl->reset();
        if (mb_stripos($url, "https://") !== false) {
            $curl
                ->setOption(CURLOPT_VERBOSE, Yii::$app->params['VERBOSE'] === 'Y')
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
        $this->result = $curl->get($url . $fst . $params);

        CurlLogger::handle($curl, Cards::MaskCardLog($url . $fst . $params), [], [], Cards::MaskCardLog($this->result));

        Yii::warning("sendCurlChech: " . $url . $fst . $params . " - " . $curl->errorCode . "\r\n", 'rsbcron');
        return $curl->errorCode == 0;
    }
}