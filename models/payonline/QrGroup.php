<?php

namespace app\models\payonline;

use Yii;

/**
 * This is the model class for table "qr_group".
 *
 * @property string $ID
 * @property string $NameGroup
 */
class QrGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qr_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['NameGroup'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'NameGroup' => 'Name Group',
        ];
    }
}
