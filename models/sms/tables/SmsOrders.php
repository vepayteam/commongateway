<?php


namespace app\models\sms\tables;


use yii\db\ActiveRecord;

/**
 * Здесь не пишется никакая логика. Только связи.
*/
class SmsOrders extends ActiveRecord
{
    public static  function tableName()
    {
        return 'sms_via_orders'; // TODO: Change the autogenerated stub
    }
}