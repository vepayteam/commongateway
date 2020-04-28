<?php

namespace app\models\partner\news;

use Yii;

/**
 * This is the model class for table "newsread".
 *
 * @property int $ID
 * @property int $IdNews
 * @property int $IdUser
 * @property int $DateRead
 */
class Newsread extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'newsread';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdNews', 'IdUser', 'DateRead'], 'required'],
            [['IdNews', 'IdUser', 'DateRead'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdNews' => 'Id News',
            'IdUser' => 'Id User',
            'DateRead' => 'Date Read',
        ];
    }
}
