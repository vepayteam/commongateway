<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "statements_planner".
 *
 * @property int $ID
 * @property int $IdPartner
 * @property int $IdTypeAcc
 * @property int $DateUpdateFrom data nachala vypiski
 * @property int $DateUpdateTo data obnovlenia vypiski
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

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdPartner', 'IdTypeAcc', 'DateUpdateFrom', 'DateUpdateTo'], 'integer'],
        ];
    }
}