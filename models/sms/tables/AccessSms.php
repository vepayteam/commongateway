<?php


namespace app\models\sms\tables;


use yii\db\ActiveRecord;

/**
 * @property integer id
 * @property string secret_key
 * @property string public_key
 * @property string description
 * @property integer partner_id
*/
class AccessSms extends ActiveRecord
{
    public static function tableName()
    {
        return 'access_sms'; // TODO: Change the autogenerated stub
    }
}