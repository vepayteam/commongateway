<?php


namespace app\models\bank;

use app\models\payonline\Cards;
use app\services\CurlLogger;
use qfsx\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

class ApplePay
{
    public function GetConf($IdPartner)
    {
        $res = Yii::$app->db->createCommand('
            SELECT
                `Apple_MerchantID`,
                `Apple_displayName`,
                `Apple_PayProcCert`,
                `Apple_KeyPasswd`,
                `Apple_MerchIdentKey`,
                `Apple_MerchIdentCert`,
                `IsUseApplepay`
            FROM
                `partner`
            WHERE
                `IsDeleted` = 0 AND `IsBlocked` = 0 AND `ID` = :IDMFO
            LIMIT 1
        ', [':IDMFO' => $IdPartner]
        )->queryOne();
        return $res;
    }

    public function ValidateSession($IdPartner, $validationURL)
    {
        $conf = $this->GetConf($IdPartner);
        $UserKey = Yii::$app->basePath . '/config/applepayclients/'.$conf['Apple_MerchIdentKey'];
        $UserCert = Yii::$app->basePath . '/config/applepayclients/'.$conf['Apple_MerchIdentCert'];

        $data = [
            "merchantIdentifier" => $conf['Apple_MerchantID'],
            "domainName" => $_SERVER['HTTP_HOST'],
            "displayName" => $conf['Apple_displayName']
        ];
        $curl = new Curl();
        $headers = [
            'Content-type: application/json'
        ];
        try {
            $curl->reset()
                ->setOption(CURLOPT_VERBOSE, Yii::$app->params['VERBOSE'] === 'Y')
                ->setOption(CURLOPT_TIMEOUT, 30)
                ->setOption(CURLOPT_CONNECTTIMEOUT, 30)
                ->setOption(CURLOPT_SSL_VERIFYHOST, false)
                ->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv12')
                ->setOption(CURLOPT_SSL_VERIFYPEER, false)
                //->setOption(CURLOPT_CAINFO, $this->caFile)
                ->setOption(CURLOPT_SSLKEY, $UserKey)
                ->setOption(CURLOPT_SSLCERT, $UserCert)
                ->setOption(CURLOPT_HTTPHEADER, $headers)
                ->setOption(CURLOPT_POST, Json::encode($data));

            if (!empty($conf['Apple_KeyPasswd'])) {
                $curl->setOption(CURLOPT_SSLKEYPASSWD, $conf['Apple_KeyPasswd']);
            }
            if (
                Yii::$app->params['DEVMODE'] != 'Y'
                && Yii::$app->params['TESTMODE'] != 'Y'
                && in_array('proxy', Yii::$app->params)
                && !empty(Yii::$app->params['proxy']['proxyHost'])
            ) {
                $curl->setOption(CURLOPT_PROXY, Yii::$app->params['proxy']['proxyHost']);
                $curl->setOption(CURLOPT_PROXYUSERPWD, Yii::$app->params['proxy']['proxyUser']);
            }
            $ans = $curl->post('https://'.$validationURL.'/paymentSession');

            CurlLogger::handle($curl, 'https://'.$validationURL.'/paymentSession', $headers, Cards::MaskCardLog($data), Cards::MaskCardLog($ans));

        } catch (\Exception $e) {
            Yii::warning("curlerror: " . $curl->responseCode . ":" . Cards::MaskCardLog($curl->response), 'merchant');
            $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
            return $ans;
        }

        return $ans;
    }
}