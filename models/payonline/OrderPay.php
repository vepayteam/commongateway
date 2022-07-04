<?php

namespace app\models\payonline;

use app\services\payment\helpers\PaymentHelper;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_pay".
 *
 * @property string $ID ID
 * @property string $IdPartner ID partner
 * @property string $DateAdd Дата выставления счета
 * @property string $DateEnd Дата окончания действия счета
 * @property string $DateOplata Дата оплаты
 * @property int $SumOrder Сумма счета
 * @property string $Comment Комментарий
 * @property string $EmailTo Адрес электронной почты
 * @property string $EmailSended Дата отправки письма
 * @property string $SmsTo Номер телефона
 * @property string $SmsSended Дата отправки смс
 * @property int $StateOrder Статус 0 - ожидает оплаты; 1 - оплачен; 2 - ошибка
 * @property string $IdPaySchet ID pay_schet
 * @property int $IdDeleted Удалено
 * @property string $OrderTo Корзина
 *
 * @property-read Partner|null $partner {@see OrderPay::getPartner()}
 */
class OrderPay extends ActiveRecord
{
    const STATUS_WAITING = 0;
    const STATUS_DONE = 1;
    const STATUS_ERROR = 2;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'order_pay';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['IdPartner', 'SumOrder'], 'required'],
            [['IdPartner', 'DateAdd', 'DateEnd', 'DateOplata', 'EmailSended', 'SmsSended', 'StateOrder', 'IdPaySchet', 'IdDeleted'], 'integer'],
            [['Comment'], 'string', 'max' => 250],
            [['EmailTo'], 'email'],
            [['SumOrder'], 'number'],
            [['EmailTo', 'SmsTo'], 'string', 'max' => 50],
            // [['OrderTo'], 'string'],
        ];
    }

    /**
     * @return string[]
     */
    public function attributeLabels(): array
    {
        return [
            'ID' => 'ID',
            'IdPartner' => 'ID Partner',
            'DateAdd' => 'Дата выставления счета',
            'DateEnd' => 'Дата окончания действия счета',
            'DateOplata' => 'Дата оплаты',
            'SumOrder' => 'Сумма счета',
            'Comment' => 'Комментарий',
            'EmailTo' => 'Адрес электронной почты',
            'EmailSended' => 'Дата отправки письма',
            'SmsTo' => 'Номер телефона',
            'SmsSended' => 'Дата отправки смс',
            'StateOrder' => 'Статус',
            'IdPaySchet' => 'ID PaySchet',
            'IdDeleted' => 'Удалено',
            'OrderTo' => 'Корзина',
        ];
    }

    /**
     * @return mixed|null
     */
    public function GetError()
    {
        $err = $this->firstErrors;
        return array_pop($err);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert): bool
    {
        if ($insert) {
            $this->DateAdd = time();
        }

        if ($this->getOldAttribute('SumOrder') !== $this->SumOrder) {
            $this->SumOrder = PaymentHelper::convertToPenny($this->SumOrder);
        }

        return parent::beforeSave($insert);
    }

    public function getPartner(): ActiveQuery
    {
        return $this->hasOne(Partner::class, ['ID' => 'IdPartner']);
    }
}
