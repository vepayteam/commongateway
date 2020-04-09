<?php


namespace app\models\antifraud\tables;


use yii\db\ActiveRecord;

/**
 * @property integer id
 * @property string asn
 * @property string provider
 * @property integer num_ips
 * @property integer num_fails
*/
class AFAsn extends ActiveRecord
{
    public static function tableName()
    {
        return 'antifraud_asn';
    }
}