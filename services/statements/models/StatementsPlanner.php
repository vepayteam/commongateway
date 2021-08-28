<?php

namespace app\services\statements\models;

use app\models\payonline\Partner;
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
    public static function tableName()
    {
        return 'statements_planner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdPartner', 'IdTypeAcc', 'DateUpdateFrom', 'DateUpdateTo'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdPartner' => 'Id Partner',
            'IdTypeAcc' => 'Id Type Acc',
            'DateUpdateFrom' => 'Date Update From',
            'DateUpdateTo' => 'Date Update To',
        ];
    }

    public function getPartner()
    {
        return $this->hasOne(Partner::class, ['ID' => 'IdPartner']);
    }
}
