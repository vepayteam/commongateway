<?php

namespace app\models\payonline;

use Yii;

/**
 * This is the model class for table "uslugatovar_types".
 *
 * @property int $Id
 * @property string $Name
 *
 * @property Uslugatovar[] $uslugatovars
 */
class UslugatovarTypes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'uslugatovar_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Id', 'Name'], 'required'],
            [['Id'], 'integer'],
            [['Name'], 'string', 'max' => 255],
            [['Id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'Name' => 'Name',
        ];
    }

    /**
     * Gets query for [[Uslugatovars]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUslugatovars()
    {
        return $this->hasMany(Uslugatovar::className(), ['iscustom' => 'id']);
    }
}
