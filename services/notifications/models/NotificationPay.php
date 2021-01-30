<?php

namespace app\services\notifications\models;

use app\services\payment\models\PaySchet;
use Yii;

/**
 * This is the model class for table "notification_pay".
 *
 * @property int $ID
 * @property int $IdPay id pay_schet
 * @property string|null $Email to email or url
 * @property string|null $url to email or url
 * @property int $TypeNotif 0 - dlia polzovatelia 1 - dlia magazina
 * @property int $DateCreate data sozdania
 * @property int $DateSend data otparavki uvedomlenia
 * @property int $SendCount chislo popytok
 * @property int $DateLastReq data zaprosa
 * @property string|null $FullReq polnuii adres zaprosa
 * @property int $HttpCode kod http otveta
 * @property string|null $HttpAns tekst http otveta
 * @property PaySchet $paySchet
 */
class NotificationPay extends \yii\db\ActiveRecord
{
    const CRON_HTTP_REQUEST_TYPE = 2;
    const QUEUE_HTTP_REQUEST_TYPE = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification_pay';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdPay'], 'required'],
            [['IdPay', 'TypeNotif', 'DateCreate', 'DateSend', 'SendCount', 'DateLastReq', 'HttpCode'], 'integer'],
            [['FullReq', 'HttpAns'], 'string'],
            [['Email'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdPay' => 'Id Pay',
            'Email' => 'Email',
            'TypeNotif' => 'Type Notif',
            'DateCreate' => 'Date Create',
            'DateSend' => 'Date Send',
            'SendCount' => 'Send Count',
            'DateLastReq' => 'Date Last Req',
            'FullReq' => 'Full Req',
            'HttpCode' => 'Http Code',
            'HttpAns' => 'Http Ans',
        ];
    }

    public function getPaySchet()
    {
        return $this->hasOne(PaySchet::className(), ['ID' => 'IdPay']);
    }

    public function getUrl()
    {
        return $this->Email;
    }

    public function setUrl($value)
    {
        $this->Email = $value;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return [
            'extid' => $this->paySchet->Extid,
            'id' => $this->paySchet->ID,
            'sum' => $this->paySchet->getFormatSummPay(),
            'status' => $this->paySchet->Status,
            'key' => $this->buildKey(),
        ];
    }

    /**
     * @param NotificationPay $notificationPay
     * @return string
     */
    public function getNotificationUrl()
    {
        return $this->paySchet->uslugatovar->UrlInform . '?' . $this->getQueryStr();
    }

    /**
     * @param NotificationPay $notificationPay
     * @return string
     */
    public function buildKey()
    {
        return md5(
            $this->paySchet->Extid
            . $this->paySchet->ID
            . $this->paySchet->getFormatSummPay()
            . $this->paySchet->Status
            . $this->paySchet->uslugatovar->KeyInform
        );
    }

    /**
     * @param array $query
     * @return string
     */
    private function getQueryStr()
    {
        $query = $this->getQuery();
        $resultArr = [];
        foreach ($query as $k => $value) {
            $resultArr[] = urlencode($k) . '=' . urlencode($value);
        }

        return implode('&', $resultArr);
    }
}
