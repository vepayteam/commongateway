<?php


namespace app\models\partner\admin;


use yii\base\Model;

/**
 * Class VyvodParts
 * @package app\models\partner\admin
 * @property int $Id
 * @property int $SenderId
 * @property int $RecipientId
 * @property int $PayschetId
 * @property int $Amount
 * @property int $DateCreate
 * @property int $Status
 *
 */
class VyvodParts extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'vyvod_parts';
    }

}
