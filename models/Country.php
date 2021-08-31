<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $Id
 * @property string $Alpha2 Двухзначный код страны.
 * @property string $Alpha3 Трехзначный код страны.
 * @property string $En Английское название страны.
 * @property string $Ru Русское название страны.
 */
class Country extends ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName(): string
    {
        return 'country';
    }
}
