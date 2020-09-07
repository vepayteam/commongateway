<?php

namespace app\models\payonline;

use Yii;

/**
 * This is the model class for table "order_pay".
 *
 * @property string $ID
 * @property string $IdPartner id partner
 * @property string $DateAdd data vystavlenia
 * @property string $DateEnd data okonchania deistvia scheta
 * @property string $DateOplata data oplaty
 * @property int $SumOrder summa scheta
 * @property string $Comment komentarii
 * @property string $EmailTo nomer dlia email
 * @property string $EmailSended data otpravki email
 * @property string $SmsTo nomer dlia sms
 * @property string $SmsSended data otpravki sms
 * @property int $StateOrder status - 0 - ojidaet 1 - oplachen 2 - otshibka
 * @property string $IdPaySchet id pay_schet
 * @property int $IdDeleted 1 - udaleno
 * @property string $OrderTo корзина
 */
class OrderPay extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_pay';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdPartner' => 'id partner',
            'DateAdd' => 'data vystavlenia',
            'DateEnd' => 'data okonchania deistvia scheta',
            'DateOplata' => 'data oplaty',
            'SumOrder' => 'Сумма счета',
            'Comment' => 'Комментарий',
            'EmailTo' => 'Адрес электронной почты',
            'EmailSended' => 'data otpravki email',
            'SmsTo' => 'Номер телефона',
            'SmsSended' => 'data otpravki sms',
            'StateOrder' => 'Статус',
            'IdPaySchet' => 'id pay_schet',
            'IdDeleted' => '1 - udaleno',
            'OrderTo' => 'корзина',
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->DateAdd = time();
        }
        $this->SumOrder = round($this->SumOrder * 100.0);
        // $this->OrderTo = json_encode($this->OrderTo);
        return parent::beforeSave($insert);
    }

    public function getPartner()
    {
        return $this->hasOne(Partner::class, ['ID' => 'IdPartner'])->one();
    }
}
