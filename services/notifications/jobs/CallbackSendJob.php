<?php


namespace app\services\notifications\jobs;


use app\services\notifications\models\NotificationPay;
use app\services\payment\models\PaySchet;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
     * @throws \Exception
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

        try {
            Yii::warning(
                'CallbackSendJob send: '.$notificationPay->getNotificationUrl(),
                'merchant'
            );
            $response = $client->request('GET', $notificationPay->getNotificationUrl());
            $notificationPay->HttpCode = $response->getStatusCode();
            $notificationPay->HttpAns = (string)$response->getBody();
        } catch (GuzzleException $e) {
            Yii::warning('CallbackSendJob GuzzleException code='.$e->getCode().'; body='.$e->getResponse()->getBody());
            $notificationPay->HttpCode = (int)$e->getCode();
            $notificationPay->HttpAns = (string)$e->getResponse()->getBody();
        } catch (\Exception $e) {
            Yii::error(sprintf(
                'CallbackSendJob execute error IdPay=%s : %s',
                $notificationPay->IdPay,
                $e->getMessage())
            );
            throw $e;
        }

        $notificationPay->DateLastReq = time();
        $notificationPay->url = $notificationPay->getNotificationUrl();
        $notificationPay->DateSend = time();
        $notificationPay->save(false);
    }
}
