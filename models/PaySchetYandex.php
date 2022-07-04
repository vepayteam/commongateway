<?php

namespace app\models;

use app\services\payment\models\PaySchet;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $paySchetId
 * @property string $messageId
 * @property string $decryptedMessage
 *
 * @property PaySchet $paySchet
 */
class PaySchetYandex extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'pay_schet_yandex';
    }

    public function getPaySchet(): ActiveQuery
    {
        return $this->hasOne(PaySchet::class, ['ID' => 'paySchetId']);
    }
}
