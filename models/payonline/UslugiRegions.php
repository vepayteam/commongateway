<?php

namespace app\models\payonline;

use Yii;

/**
 * This is the model class for table "uslugi_regions".
 *
 * @property string $Id
 * @property string $NameRegion
 */
class UslugiRegions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uslugi_regions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['NameRegion'], 'required'],
            [['NameRegion'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'NameRegion' => 'region uslugi',
        ];
    }
}
