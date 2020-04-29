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
 * @property int $Bank
 * @property int $BankId
 * @property int $BankDate
 * @property int $IsDeleted
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
            [['DateAdd'], 'required'],
            [['DateAdd', 'DateSend', 'Bank', 'BankId', 'BankDate', 'IsDeleted'], 'integer'],
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
            'Head' => 'Заголовок',
            'Body' => 'Новость',
        ];
    }

    public function getNewsread()
    {
        return $this->hasOne(Newsread::class, ['IdNews'=>'ID']);
    }
}
