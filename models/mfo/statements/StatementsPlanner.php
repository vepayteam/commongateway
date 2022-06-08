<?php

namespace app\models\mfo\statements;

use yii\db\ActiveRecord;

/**
 * Class StatementsPlanner
 *
 * @package app\models\mfo\statements
 * @property int  $ID
 * @property int  $IdPartner
 * @property int  $IdTypeAcc
 * @property int  $DateUpdateFrom
 * @property int  $DateUpdateTo
 */
class StatementsPlanner extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'statements_planner';
    }
}
