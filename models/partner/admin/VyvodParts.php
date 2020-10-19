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
    const STATUS_CREATED = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_ERROR = 2;

    public static function tableName()
    {
        return 'vyvod_parts';
    }

}
