<?php


namespace app\services\notifications\jobs;


use app\models\payonline\Cards;
use app\models\planner\Notification;
use app\services\DeprecatedCurlLogger;
use app\services\notifications\models\NotificationPay;
use app\services\payment\models\PaySchet;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\helpers\StringHelper;
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
        $notificationPay = $this->notificationPay;

        if (in_array($notificationPay->paySchet->Status, [PaySchet::STATUS_DONE, PaySchet::STATUS_ERROR])) {

            Yii::warning(sprintf('CallbackSendJob hasNotificationPay: %s : %s', $this->notificationPayId, Json::encode($this->notificationPay->getAttributes())));
            if(empty($notificationPay->paySchet->uslugatovar->UrlInform)) {
                Yii::warning('CallbackSendJob execute UrlInformEmpty: ' . Json::encode(['notificationPay' => $this->notificationPayId]));
            }

            $curl = curl_init();
            Yii::warning('CallbackSendJob sendEnd: ' . $this->notificationPayId);
            $headers = [
                "Content-Type: application/json"
            ];
            curl_setopt_array($curl, array(
                CURLOPT_URL => $notificationPay->getNotificationUrl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => $headers,
            ));

            try {
                $startTimeExec = microtime(true);
                $response = curl_exec($curl);

                (new DeprecatedCurlLogger(curl_getinfo($curl), $notificationPay->getNotificationUrl(), $headers, [], Cards::MaskCardLog($response)))();

                Yii::warning(sprintf(
                        'CallbackSendJob response: %s ;  %s',
                        $this->notificationPayId,
                        $response
                    )
                );
                Yii::warning(sprintf(
                        'CallbackSendJob timeExec: %s ;  %s',
                        $this->notificationPayId,
                        microtime(true) - $startTimeExec
                    )
                );

            } catch (\Exception $e) {
                Yii::error(sprintf(
                        'CallbackSendJob execute error id=%s : %s',
                        $notificationPay->IdPay,
                        $e->getMessage())
                );
            }

            $curlError = curl_error($curl);
            if($curlError) {
                Yii::error(sprintf(
                        'CallbackSendJob curlError: %s ;  %s',
                        $this->notificationPayId,
                        $curlError
                    )
                );
            }

            Yii::warning(sprintf('CallbackSendJob afterTry: %s', $this->notificationPayId));
            $info = curl_getinfo($curl);
            $notificationPay->HttpCode = $info['http_code'];
            $notificationPay->HttpAns = StringHelper::truncate($response, Notification::HTTP_ANS_MAX_LENGTH, Notification::HTTP_ANS_SUFFIX);

            $notificationPay->DateLastReq = time();
            $notificationPay->url = $notificationPay->getNotificationUrl();
            $notificationPay->DateSend = time();
            $saveResult = $notificationPay->save(false);
            Yii::warning(sprintf('CallbackSendJob save: %s ; %s', $this->notificationPayId, $saveResult));
            curl_close($curl);
        }
    }
}
