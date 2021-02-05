<?php


namespace app\services\notifications;


use app\models\queue\JobPriorityInterface;
use app\services\notifications\jobs\CallbackSendJob;
use app\services\notifications\models\NotificationPay;
use app\services\payment\models\PaySchet;
use Yii;

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
}
