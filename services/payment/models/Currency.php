<?php

namespace app\services\payment\models;

use yii\db\ActiveRecord;

/**
 * @property string Name
 * @property int Number
 * @property int Code
 */
class Currency extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'currency';
    }
    public function rules(): array
    {
        return [
            [['Code'], 'required'],
            [['Number'], 'integer'],
            [['Name', 'Code'], 'string', 'max' => 250],
        ];
    }
}
