<?php

namespace app\services\ident\models;

use Yii;

/**
 * This is the model class for table "ident_runa".
 *
 * @deprecated Удалено все, что связано с руной, основную таблицу оставляем как есть
 *
 * @property int $Id
 * @property int $PartnerId
 * @property string|null $Data
 * @property int|null $DateCreate
 * @property int|null $Tid
 */
class IdentRuna extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ident_runa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PartnerId', 'Tid'], 'required'],
            [['PartnerId', 'DateCreate', 'Tid'], 'integer'],
            [['Data'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'PartnerId' => 'Partner ID',
            'Status' => 'Status',
            'Data' => 'Data',
            'DateCreate' => 'Date Create',
            'DateLastUpdate' => 'Date Last Update',
        ];
    }
}
