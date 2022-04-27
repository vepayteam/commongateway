<?php

namespace app\models;

use app\services\payment\models\Bank;

/**
 * This is the model class for table "options".
 *
 * @property int $ID
 * @property string|null $Name opcia
 * @property string|null $Value znachenie
 */
class Options extends \yii\db\ActiveRecord
{
    /** Holiday list. @todo Rename to "holiday_list". */
    public const NAME_DISABLED_DAY = 'disabledday';
    /** ID of {@see Bank} to make payment through. */
    public const NAME_BANK_PAYMENT_ID = 'bank_payment_id';
    /** ID of {@see Bank} for transferring to card. */
    public const NAME_BANK_TRANSFER_TO_CARD_ID = 'bank_transfer_to_card_id';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'options';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Name', 'Value'], 'string', 'max' => 255],
            [['Name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Name' => 'Name',
            'Value' => 'Value',
        ];
    }

    public static function getAllToArray()
    {
        $result = [];
        foreach (self::find()->all() as $option) {
            $result[$option->Name] = $option->Value;
        }
        return $result;
    }

    /**
     * Returns an option by the specified name.
     * If the option doesn't exist a new one with the given name returned.
     *
     * @param string $name
     * @return static
     */
    public static function getOption(string $name): Options
    {
        return static::findOne(['Name' => $name]) ?? new static(['Name' => $name]);
    }
}
