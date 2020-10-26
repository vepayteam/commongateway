<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "options".
 *
 * @property int $ID
 * @property string|null $Name opcia
 * @property string|null $Value znachenie
 */
class Options extends \yii\db\ActiveRecord
{
    const BASE_BANK_BY_TU_NAME_SUFFIX = 'base_bank_name__';

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


}
