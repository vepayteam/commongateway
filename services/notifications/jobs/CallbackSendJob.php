<?php


namespace app\services\notifications\jobs;


use app\services\notifications\models\NotificationPay;
use app\services\payment\models\PaySchet;
use GuzzleHttp\Client;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\Queue;

class CallbackSendJob extends BaseObject implements \yii\queue\JobInterface
{
    public $notificationPayId;

    /** @var NotificationPay */
    private $notificationPay;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $this->notificationPay = NotificationPay::findOne(['ID' => $this->notificationPayId]);
        $notificationPay = $this->notificationPay;
        if(empty($notificationPay->paySchet->uslugatovar->UrlInform)) {
            Yii::warning('CallbackSendJob execute UrlInformEmpty: ' . Json::encode(['notificationPay' => $this->notificationPayId]));
        }

        $client = new Client([
            'timeout'  => 120,
        ]);

        $query = [
            'extid' => $notificationPay->paySchet->Extid,
            'id' => $notificationPay->paySchet->ID,
            'sum' => $this->formatSummPay(),
            'status' => $notificationPay->paySchet->Status,
            'key' => $this->buildKey(),
        ];
        
        $response = $client->request('GET', $notificationPay->url, [
            'query' => $query,
        ]);

        $notificationPay->url = $notificationPay->url . '?' . $this->getQueryStr($query);
        $notificationPay->DateSend = time();
        $notificationPay->HttpAns = (string)$response->getBody();
        $notificationPay->save(false);
    }

    /**
     * @return string
     */
    private function formatSummPay()
    {
        return sprintf("%02.2f", $this->notificationPay->paySchet->SummPay / 100.0);
    }

    /**
     * @return string
     */
    private function buildKey()
    {
        return md5(
            $this->notificationPay->paySchet->Extid
            . $this->notificationPay->paySchet->ID
            . $this->formatSummPay()
            . $this->notificationPay->paySchet->Status
            . $this->notificationPay->paySchet->uslugatovar->KeyInform
        );
    }

    /**
     * @param array $query
     * @return string
     */
    private function getQueryStr($query)
    {
        $resultArr = [];
        foreach ($query as $k => $value) {
            $resultArr[] = urlencode($k) . '=' . urlencode($value);
        }

        return implode('&', $resultArr);
    }
}
