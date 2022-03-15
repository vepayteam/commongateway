<?php


namespace app\services\notifications;


use app\models\queue\JobPriorityInterface;
use app\services\notifications\jobs\CallbackSendJob;
use app\services\notifications\models\NotificationPay;
use app\services\payment\models\PaySchet;
use Yii;
use yii\helpers\Json;

class NotificationsService
{

    /**
     * @param PaySchet $paySchet
     * @return bool
     * @throws \yii\db\Exception
     */
    public function addNotificationByPaySchet(PaySchet $paySchet)
    {
        if (!empty($paySchet->UserEmail) && $paySchet->Status == PaySchet::STATUS_DONE) {
            //для плательщика чек
            Yii::$app->db->createCommand()
                ->insert('notification_pay', [
                    'IdPay' => $paySchet->ID,
                    'Email' => $paySchet->UserEmail,
                    'TypeNotif' => 0,
                    'DateCreate' => time(),
                    'DateSend' => 0
                ])
                ->execute();
        }

        // TODO: проверить необходимость
        if (false && in_array($paySchet->TypeWidget, [0, 1]) && !empty($paySchet->UserUrlInform)) {
            //http
            Yii::$app->db->createCommand()
                ->insert('notification_pay', [
                    'IdPay' => $paySchet->ID,
                    'Email' => $paySchet->UserUrlInform,
                    'TypeNotif' => 3,
                    'DateCreate' => time(),
                    'DateSend' => 0
                ])
                ->execute();
        }

        //по email успешные
        if (!empty($paySchet->uslugatovar->EmailReestr) && $paySchet->Status == PaySchet::STATUS_DONE) {
            Yii::$app->db->createCommand()
                ->insert('notification_pay', [
                    'IdPay' => $paySchet->ID,
                    'Email' => $paySchet->uslugatovar->EmailReestr,
                    'TypeNotif' => 1,
                    'DateCreate' => time(),
                    'DateSend' => 0
                ])
                ->execute();
        }

        //по http успешные и нет
        if (!empty($paySchet->uslugatovar->UrlInform)) {
            $notificationPay = new NotificationPay();
            $notificationPay->IdPay = $paySchet->ID;
            $notificationPay->url = $notificationPay->getNotificationUrl();
            $notificationPay->TypeNotif = NotificationPay::QUEUE_HTTP_REQUEST_TYPE;
            $notificationPay->DateCreate = time();
            $notificationPay->DateSend = 0;
            $notificationPay->save();

            Yii::$app->queue->push(new CallbackSendJob([
                'notificationPayId' => $notificationPay->ID,
            ]));
        }
        return true;
    }

    public function sendPostbacks(PaySchet $paySchet)
    {
        // Если платеж не в ожидание, и у платежа имеется PostbackUrl, отправляем
        // TODO: in strategy
        if(!empty($paySchet->PostbackUrl)
            && in_array($paySchet->Status, [PaySchet::STATUS_DONE, PaySchet::STATUS_ERROR, PaySchet::STATUS_CANCEL])
        ) {
            $data = [
                'status' => $paySchet->Status,
                'message' => $paySchet->ErrorInfo,
                'id' => $paySchet->ID,
                'amount' => $paySchet->SummPay,
                'extid' => $paySchet->Extid,
                'card_num' => $paySchet->CardNum,
                'card_holder' => $paySchet->CardHolder,
            ];

            // TODO: queue
            try {
                Yii::warning("NotificationsService sendPostbacks PostbackUrl: " . Json::encode($data));
                $this->sendPostbackRequest($paySchet->PostbackUrl, $data);
            } catch (\Exception $e) {
                Yii::warning("Error $paySchet->ID postbackurl: ".$e->getMessage());
            }
        }

        if(!empty($paySchet->PostbackUrl_v2)
            && in_array($paySchet->Status, [PaySchet::STATUS_DONE, PaySchet::STATUS_ERROR, PaySchet::STATUS_CANCEL])
        ) {
            $data = [
                'status' => $paySchet->Status,
                'message' => $paySchet->ErrorInfo,
                'id' => $paySchet->ID,
                'amount' => $paySchet->SummPay,
                'extid' => $paySchet->Extid,
                'fullname' => $paySchet->FIO,
                'document_id' => $paySchet->Dogovor,
            ];

            // TODO: queue
            try {
                Yii::warning("NotificationsService sendPostbacks PostbackUrl_v2: " . Json::encode($data));
                $this->sendPostbackRequest($paySchet->PostbackUrl_v2, $data);
            } catch (\Exception $e) {
                Yii::warning("Error $paySchet->ID postbackurl: ".$e->getMessage());
            }
        }
    }

    private function sendPostbackRequest($url, $data)
    {
        // TODO: refact to service
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_VERBOSE => Yii::$app->params['VERBOSE'] === 'Y',
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $startTimestamp = microtime(true);
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);

        $logData = [
            'url' => $url,
            'data' => $data,
            'response' => $response,
            'time' => microtime(true) - $startTimestamp,
            'error' => $error,
            'info' => $info,
        ];

        Yii::warning("NotificationsService postback : " . Json::encode($logData));
        curl_close($curl);
    }
}
