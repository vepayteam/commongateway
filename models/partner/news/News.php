<?php

namespace app\models\partner\news;

use Yii;

/**
 * This is the model class for table "news".
 *
 * @property int $ID
 * @property string|null $Head
 * @property string|null $Body
 * @property int $DateAdd
 * @property int $DateSend
 */
class News extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Body'], 'string'],
            [['DateAdd', 'DateSend'], 'required'],
            [['DateAdd', 'DateSend'], 'integer'],
            [['Head'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Head' => 'Head',
            'Body' => 'Body',
            'DateAdd' => 'Date Add',
            'DateSend' => 'Date Send',
        ];
    }

    public function getNewsread()
    {
        return $this->hasOne(Newsread::class, ['IdNews'=>'ID']);
    }
}
