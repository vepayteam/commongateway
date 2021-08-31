<?php


namespace app\models;


use yii\db\ActiveRecord;

/**
 * Class PaySchetPart
 * @package app\models
 *
 * @property int $id
 * @property int $PayschetId
 * @property int $PartnerId
 * @property int $Amount
 * @property int $VyvodId
 */
class PayschetPart extends ActiveRecord
{

    public static function tableName()
    {
        return 'pay_schet_parts';
    }


}
