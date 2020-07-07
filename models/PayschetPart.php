<?php


namespace app\models;


use yii\db\ActiveRecord;

/**
 * Class PaySchetPart
 * @package app\models
 *
 * @param int $id
 * @param int $pay_schet_id
 * @param int $summ
 */
class PayschetPart extends ActiveRecord
{

    public static function tableName()
    {
        return 'pay_schet_parts';
    }


}
