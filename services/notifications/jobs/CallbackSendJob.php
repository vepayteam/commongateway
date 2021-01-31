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
        
        $response = $client->request('GET', $notificationPay->getNotificationUrl(), [
            'query' => $notificationPay->getQuery(),
        ]);

        $notificationPay->HttpCode = $response->getStatusCode();
        $notificationPay->DateLastReq = time();
        $notificationPay->url = $notificationPay->getNotificationUrl();
        $notificationPay->DateSend = time();
        $notificationPay->HttpAns = (string)$response->getBody();
        $notificationPay->save(false);
    }
}
