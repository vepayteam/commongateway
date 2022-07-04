<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $keyValue
 * @property int $keyExpiration
 * @property string $protocolVersion
 */
class YandexPayRootKey extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'yandex_pay_root_keys';
    }
}
