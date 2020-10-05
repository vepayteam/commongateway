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

    const LIST = [
        self::CARD_REG_TEXT_HEADER_NAME => [
            'title' => 'Текст сообщения при регистрации карты',
            'type' => 'textarea',
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
