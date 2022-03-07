<?php

namespace app\services\notifications\models;

use app\models\partner\PartnerCallbackSettings;
use app\models\payonline\Cards;
use app\services\payment\models\PaySchet;

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
    public function getQuery(): array
    {
        $params = [];

        $settings = PartnerCallbackSettings::getByPartnerId($this->paySchet->partner->ID);
        if ($settings->SendExtId) {
            $params['extid'] = $this->paySchet->Extid;
        }

        if ($settings->SendId) {
            $params['id'] = $this->paySchet->ID;
        }

        if ($settings->SendSum) {
            $params['sum'] = $this->paySchet->getFormatSummPay();
        }

        if ($settings->SendStatus) {
            $params['status'] = $this->paySchet->Status;
        }

        if ($settings->SendChannel) {
            $params['channel'] = $this->paySchet->bank->ChannelName;
        }

        if ($settings->SendCardMask) {
            $params['card'] = Cards::MaskCard($this->paySchet->CardNum);
        }

        if ($settings->SendErrorCode) {
            $params['errorCode'] = $this->getRcCode();
        }

        $params['key'] = $this->buildKey($settings);

        return $params;
    }

    /**
     * @param NotificationPay $notificationPay
     * @return string
     */
    public function getNotificationUrl()
    {
        $urlInformArr = explode('?', $this->paySchet->uslugatovar->UrlInform);

        $delimiter = '?';
        if(count($urlInformArr) == 2) {
            return $this->paySchet->uslugatovar->UrlInform . '&' . $this->getQueryStr();
        }

        return $this->paySchet->uslugatovar->UrlInform . $delimiter . $this->getQueryStr();
    }

    /**
     * @param PartnerCallbackSettings $settings
     * @return string
     */
    public function buildKey(PartnerCallbackSettings $settings): string
    {
        $params = [];
        if ($settings->SendExtId) {
            $params[] = $this->paySchet->Extid;
        }

        if ($settings->SendId) {
            $params[] = $this->paySchet->ID;
        }

        if ($settings->SendSum) {
            $params[] = $this->paySchet->getFormatSummPay();
        }

        if ($settings->SendStatus) {
            $params[] = $this->paySchet->Status;
        }

        if ($settings->SendChannel) {
            $params[] = $this->paySchet->bank->Name;
        }

        if ($settings->SendCardMask) {
            $params[] = Cards::MaskCard($this->paySchet->CardNum);
        }

        if ($settings->SendErrorCode) {
            $params[] = $this->getRcCode();
        }

        $params[] = $this->paySchet->uslugatovar->KeyInform;
        $key = join('', $params);

        return md5($key);
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

    /**
     * Возвращает RCCode
     *
     * @return string
     */
    private function getRcCode(): string
    {
        /**
         * VPBC-1294
         * Для успешных операций всегда возвращаем '0'
         * Если статус операции не успех и rcCode пустой, то возвращаем 'X'
         */
        if ($this->paySchet->Status === PaySchet::STATUS_DONE) {
            return '0';
        } else if (empty($this->paySchet->RCCode)) {
            return 'X';
        }

        return $this->paySchet->RCCode;
    }
}
