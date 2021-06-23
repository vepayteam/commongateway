<?php

namespace app\services\partners\models;

use app\models\payonline\Partner;
use Yii;

/**
 * This is the model class for table "partner_options".
 *
 * @property int $Id
 * @property int $PartnerId
 * @property string $Name
 * @property string|null $Value
 */
class PartnerOption extends \yii\db\ActiveRecord
{
    const CARD_REG_TEXT_HEADER_NAME = 'card-reg__text-header';

    const EMAILS_BY_SEND_LATE_UPDATE_PAY_SCHETS_NAME = 'payment__emails_by_send_late_update_payschet';
    const DELTA_TIME_LATE_UPDATE_PAY_SCHETS_NAME = 'payment__delta_time_late_update_payschet';

    const LIST = [
        self::CARD_REG_TEXT_HEADER_NAME => [
            'title' => 'Текст сообщения при регистрации карты',
            'type' => 'textarea',
            'default' => 'Для проверки банковской карты с нее будет списана, затем возвращена случайная сумма до 10р.',
        ],
        self::EMAILS_BY_SEND_LATE_UPDATE_PAY_SCHETS_NAME => [
            'title' => 'Emails для отправки реестров с поздним обновлением статуса, через запятую',
            'type' => 'text',
            'default' => '',
        ],
        self::DELTA_TIME_LATE_UPDATE_PAY_SCHETS_NAME => [
            'title' => 'Время, после которого обновление статуса считать поздним, в секундах',
            'type' => 'number',
            'default' => '18000',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'partner_options';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PartnerId', 'Name'], 'required'],
            [['PartnerId'], 'integer'],
            [['Value'], 'string'],
            [['Name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'PartnerId' => 'Partner ID',
            'Name' => 'Name',
            'Value' => 'Value',
        ];
    }

    public function getPartner()
    {
        return $this->hasOne(Partner::class, ['ID' => 'PartnerId']);
    }
}
