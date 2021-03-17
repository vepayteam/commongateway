<?php

namespace app\models\mfo;

/**
 * Class VyvodReestr
 *
 * @package app\models\mfo
 * @property int  $ID            [int(10) unsigned]
 * @property int  $IdPartner     [int(10) unsigned]  id partners
 * @property int  $DateFrom      [int(10) unsigned]  data c
 * @property int  $DateTo        [int(10) unsigned]  data po
 * @property int  $DateOp        [int(10) unsigned]  data operacii
 * @property int  $SumOp         [int(10) unsigned]  summa
 * @property bool $StateOp       [tinyint(1) unsigned]  status - 0 - v obrabotke 1 - ispolnena 2 - otmeneno
 * @property int  $IdPay         [int(10) unsigned]  id pay_schet
 * @property bool $TypePerechisl [tinyint(1) unsigned]  0 - perevod na vydachu 1 - perechislene na schet
 */
class VyvodReestr extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vyvod_reestr';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID'
        ];
    }
}
