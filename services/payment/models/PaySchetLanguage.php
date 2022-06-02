<?php

namespace app\services\payment\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $paySchetId
 * @property string $apiLanguage трехбуквенное обозначение языка в стандарте ISO 639-3
 *
 * @property-read PaySchet $paySchet {@see PaySchetLanguage::getPaySchet()}
 */
class PaySchetLanguage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'pay_schet_language';
    }

    public function getPaySchet(): ActiveQuery
    {
        return $this->hasOne(PaySchet::class, ['ID' => 'paySchetId']);
    }
}