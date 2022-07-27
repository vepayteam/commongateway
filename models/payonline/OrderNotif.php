<?php

namespace app\models\payonline;

use app\helpers\UrlHelper;
use app\models\SendEmail;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Class OrderNotif
 *
 * @property int ID ID
 * @property int IdOrder ID Order
 * @property int DateAdd Дата создания
 * @property int DateSended Дата отправки
 * @property int TypeSend Тип отправки константы TYPE_SEND_*
 * @property int StateSend Статус отправки константы STATE_SEND_*
 *
 * @property-read OrderPay $orderPay
 */
class OrderNotif extends ActiveRecord
{
    /** Тип отправки email */
    const TYPE_SEND_EMAIL = 0;

    /** Тип отправки смс */
    const TYPE_SEND_SMS = 1;


    /** Статус в очереди */
    const STATE_SEND_WAIT = 0;

    /** Статус успешно */
    const STATE_SEND_SUCCESS = 1;

    /** Статус ошибка */
    const STATE_SEND_ERROR = 2;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'order_notif';
    }

    /**
     * @return ActiveQuery
     */
    public function getOrderPay(): ActiveQuery
    {
        return $this->hasOne(OrderPay::class, ['ID' => 'IdOrder']);
    }

    /**
     * @param OrderPay $orderPay
     */
    public function SendNotif(OrderPay $orderPay)
    {
        if (!empty($orderPay->EmailTo)) {
            self::addOrderNotif($orderPay, self::TYPE_SEND_EMAIL);

            $orderPay->EmailSended = time();
            $orderPay->save(false);
        }

        if (!empty($orderPay->SmsTo)) {
            self::addOrderNotif($orderPay, self::TYPE_SEND_SMS);

            $orderPay->SmsSended = time();
            $orderPay->save(false);
        }
    }

    public function SendEmails()
    {
        $orderNotifList = OrderNotif::find()
            ->alias('orderNotifyAlias')
            ->joinWith('orderPay orderPayAlias')
            ->where([
                'orderPayAlias.StateOrder' => self::STATE_SEND_WAIT,
                'orderNotifyAlias.DateSended' => 0,
                'orderNotifyAlias.TypeSend' => self::TYPE_SEND_EMAIL
            ])
            ->all();

        /** @var OrderNotif $orderNotif */
        foreach ($orderNotifList as $orderNotif) {
            $orderPay = $orderNotif->orderPay;

            $subject = 'Счет на оплату';
            $content = Yii::$app->controller->renderPartial('@app/mail/order_notif', [
                'apiUrl' => UrlHelper::getApiUrl(),
                'orderNotif' => $orderNotif,
                'orderPay' => $orderPay,
                'orderTo' => $orderPay->OrderTo ? Json::decode($orderPay->OrderTo) : null,
            ]);

            $sendEmail = new SendEmail();
            $sendEmail->send($orderPay->EmailTo, null, $subject, $content);

            $orderNotif->DateSended = time();
            $orderNotif->StateSend = self::STATE_SEND_SUCCESS;
            $orderNotif->save(false);
        }
    }

    private static function addOrderNotif(OrderPay $orderPay, int $typeSend)
    {
        $orderNotif = new OrderNotif();
        $orderNotif->IdOrder = $orderPay->ID;
        $orderNotif->DateAdd = time();
        $orderNotif->DateSended = 0;
        $orderNotif->TypeSend = $typeSend;
        $orderNotif->StateSend = self::STATE_SEND_WAIT;
        $orderNotif->save(false);
    }
}
