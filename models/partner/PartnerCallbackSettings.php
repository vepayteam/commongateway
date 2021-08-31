<?php

namespace app\models\partner;

use app\models\payonline\Partner;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "partner_callback_settings".
 *
 * @property int $Id
 * @property int $PartnerId
 * @property bool $SendExtId
 * @property bool $SendId
 * @property bool $SendSum
 * @property bool $SendStatus
 * @property bool $SendChannel
 *
 * @property-read Partner $partner
 */
class PartnerCallbackSettings extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'partner_callback_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['Id', 'PartnerId'], 'integer'],
            [['SendExtId', 'SendId', 'SendSum', 'SendStatus', 'SendChannel'], 'boolean'],
        ];
    }

    /**
     * Возвращает PartnerCallbackSettings по ID партнера или создает дефолтную модель
     *
     * @param int $partnerId
     * @return PartnerCallbackSettings
     */
    public static function getByPartnerId(int $partnerId): PartnerCallbackSettings
    {
        /** @var PartnerCallbackSettings $settings */
        $settings = PartnerCallbackSettings::find()
            ->where(['PartnerId' => $partnerId])
            ->one();

        if ($settings) {
            return $settings;
        }

        return self::getDefaultInstance($partnerId);
    }

    /**
     * Возвращает дефолтную модель
     *
     * @param int $partnerId
     * @return PartnerCallbackSettings
     */
    private static function getDefaultInstance(int $partnerId): PartnerCallbackSettings
    {
        $settings = new PartnerCallbackSettings();
        $settings->PartnerId = $partnerId;
        $settings->SendExtId = true;
        $settings->SendId = true;
        $settings->SendSum = true;
        $settings->SendStatus = true;
        $settings->SendChannel = false;

        return $settings;
    }

    public function getPartner(): ActiveQuery
    {
        return $this->hasOne(Partner::class, ['ID' => 'PartnerId']);
    }
}
