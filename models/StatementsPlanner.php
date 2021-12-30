<?php


namespace app\models;

/**
 * This is the model class for table "statements_account".
 *
 * @property int $ID
 * @property int $IdPartner
 * @property int $TypeAccount
 * @property int|null $BankId
 * @property int $IdTypeAcc
 * @property int $DateUpdateFrom
 * @property int $IDateUpdateTo
 */
class StatementsPlanner extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'statements_planner';
    }

}
