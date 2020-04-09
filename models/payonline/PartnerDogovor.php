<?php

namespace app\models\payonline;

use Yii;

/**
 * This is the model class for table "partner_dogovor".
 *
 * @property int $ID [int(10) unsigned]
 * @property int $IdPartner [int(10) unsigned]  id partner
 * @property string $NameMagazin [varchar(100)]  naimenovane magazina
 * @property int $TypeMagazin [int(11)]  tip: 0 - inet 1 - torgov 2 - mobile
 * @property string $NumDogovor [varchar(20)]  nomer
 * @property string $DateDogovor [varchar(20)]  data
 * @property string $PodpisantFull [varchar(100)]  podpisant polnostyu
 * @property string $PodpisantShort [varchar(50)]  podpisant kratko
 * @property string $PodpDoljpost [varchar(50)]  doljnost
 * @property string $PodpOsnovan [varchar(100)]  osnodanie
 * @property string $Adres [varchar(1000)]  adres ili url
 * @property bool $IsDeleted [tinyint(1) unsigned]  1 - udalen
 */
class PartnerDogovor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_dogovor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['IdPartner'], 'required'],
            [['IdPartner', 'TypeMagazin'], 'integer'],
            [['NumDogovor', 'DateDogovor'], 'string', 'max' => 20],
            [['NameMagazin', 'PodpisantFull', 'PodpOsnovan'], 'string', 'max' => 100],
            [['PodpisantShort', 'PodpDoljpost'], 'string', 'max' => 50],
            [['Adres'], 'string', 'max' => 1000],
            [['IsDeleted'], 'integer', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdPartner' => 'id partner',
            'NameMagazin' => 'Название магазина',
            'TypeMagazin' => 'Тип',//0 - inet 1 - torgov 2 - mobile
            'NumDogovor' => 'Номер',
            'DateDogovor' => 'Дата',
            'PodpisantFull' => 'podpisant polnostyu',
            'PodpisantShort' => 'podpisant kratko',
            'PodpDoljpost' => 'doljnost',
            'PodpOsnovan' => 'osnodanie',
            'Adres' => 'Адрес',
            'IsDeleted' => '1 - udalen',
        ];
    }
}
