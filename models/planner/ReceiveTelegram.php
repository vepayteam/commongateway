<?php

namespace app\models\planner;

use app\models\partner\news\News;
use app\models\payonline\Cards;
use app\services\CurlLogger;
use qfsx\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

class ReceiveTelegram
{
    private $resultText;

    public function execute()
    {
        $bank = 2;

        $host = 'https://test.vepay.online';
        //$host = 'http://127.0.0.1:8000';

        $ret = '';
        if ($this->CurlGet($host.'/site/feed/?bank='.$bank, '')) {
            $ret = $this->resultText;
        }
        echo ($ret."\r\n");
        try {
            $ret = Json::decode($ret);
            if ($ret['status'] == 1) {
                $ret = $ret['data'];
            } else {
                $ret = null;
            }
        } catch (\Throwable $e) {
            Yii::warning($e->getMessage(), 'rsbcron');
            $ret = null;
        }
        if ($ret) {
            array_reverse($ret);
            foreach ($ret as $mesg) {
                $news = News::findOne(['Bank' => $bank, 'BankId' => $mesg['id']]);
                if (!$news) {
                    $news = new News();
                    $news->setAttributes([
                        'Head' => 'Оповещение от Банка',
                        'Body' => $mesg['message'],
                        'DateAdd' => strtotime($mesg['date']) + 10800,
                        'Bank' => $bank,
                        'BankId' => $mesg['id'],
                        'BankDate' => strtotime($mesg['date']),
                    ]);
                    $news->save(false);
                }
            }
        }
    }

    public function CurlGet($url, $params)
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
        if (Yii::$app->params['DEVMODE'] != 'Y' && Yii::$app->params['TESTMODE'] != 'Y'
            && in_array('proxy', Yii::$app->params) && !empty(Yii::$app->params['proxy']['proxyHost'])) {
            $curl->setOption(CURLOPT_VERBOSE, Yii::$app->params['VERBOSE'] === 'Y');
            $curl->setOption(CURLOPT_PROXY, Yii::$app->params['proxy']['proxyHost']);
            $curl->setOption(CURLOPT_PROXYUSERPWD, Yii::$app->params['proxy']['proxyUser']);
        }
        $curl->get($url . $fst . $params);

        CurlLogger::handle($curl, Cards::MaskCardLog($url . $fst . $params), [], [], Cards::MaskCardLog($curl->response));

        Yii::warning("sendCurlGet-url: " . $url . $fst . $params . "\r\n", 'rsbcron');
        if ($curl->errorCode) {
            $this->resultText = $curl->errorText;
            Yii::warning("sendCurlGet-code: code=" . $curl->errorCode . ": " . $curl->errorText . "\r\n", 'rsbcron');
        } else {
            $this->resultText = $curl->response;
            Yii::warning("sendCurlGet-code: http=" . mb_substr(trim(preg_replace("/\s+/", " ", strip_tags($curl->responseCode))), 0, 1024) . "\r\n", 'rsbcron');
        }
        Yii::warning("sendCurlGet-response: ". mb_substr($curl->response, 0, 250) . "\r\n", 'rsbcron');

        return $curl->errorCode == 0 && $curl->responseCode < 500;
    }


}