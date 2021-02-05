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
        Yii::warning('CallbackSendJob start: ' . $this->notificationPayId);
        $this->notificationPay = NotificationPay::findOne(['ID' => $this->notificationPayId]);
        Yii::warning(sprintf('CallbackSendJob hasNotificationPay: %s : %s', $this->notificationPayId, Json::encode($this->notificationPay->getAttributes())));
        $notificationPay = $this->notificationPay;
        if(empty($notificationPay->paySchet->uslugatovar->UrlInform)) {
            Yii::warning('CallbackSendJob execute UrlInformEmpty: ' . Json::encode(['notificationPay' => $this->notificationPayId]));
        }

        $curl = curl_init();
        Yii::warning('CallbackSendJob sendEnd: ' . $this->notificationPayId);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $notificationPay->getNotificationUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        try {
            $response = curl_exec($curl);
            Yii::warning(sprintf(
                    'CallbackSendJob response: %s ;  %s',
                    $this->notificationPayId,
                    $response
                )
            );

        } catch (\Exception $e) {
            Yii::error(sprintf(
                    'CallbackSendJob execute error id=%s : %s',
                    $notificationPay->IdPay,
                    $e->getMessage())
            );
        }

        Yii::warning(sprintf('CallbackSendJob afterTry: %s', $this->notificationPayId));
        $info = curl_getinfo($curl);
        $notificationPay->HttpCode = $info['http_code'];
        $notificationPay->HttpAns = $response;

        $notificationPay->DateLastReq = time();
        $notificationPay->url = $notificationPay->getNotificationUrl();
        $notificationPay->DateSend = time();
        $saveResult = $notificationPay->save(false);
        Yii::warning(sprintf('CallbackSendJob save: %s ; %s', $this->notificationPayId, $saveResult));
        curl_close($curl);
    }
}
