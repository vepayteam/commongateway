<?php

namespace app\models\mfo;

/**
 * Class VyvodSystem
 *
 * @package app\models\mfo
 * @property int  $ID        [int(10) unsigned]
 * @property int  $DateOp    [int(10) unsigned]  data operacii
 * @property int  $IdPartner [int(10) unsigned]  id partner
 * @property int  $DateFrom  [int(10) unsigned]  data s
 * @property int  $DateTo    [int(10) unsigned]  data po
 * @property int  $Summ      [int(10) unsigned]  summa v kop
 * @property bool $SatateOp  [tinyint(3) unsigned]  status - 1 - ispolneno 0 - v rabote 2 - ne ispolneno
 * @property int  $IdPay     [int(10) unsigned]  id pay_schet
 * @property bool $TypeVyvod [tinyint(1) unsigned]  tip - 0 - pogashenie 1 - vyplaty
 */
class VyvodSystem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vyvod_system';
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
