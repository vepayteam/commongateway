<?php

namespace app\models\partner\stat;

use app\models\partner\admin\VyvodVoznag;
use app\models\payonline\Partner;
use Yii;

/**
 * This is the model class for table "act_schet".
 *
 * @property int $ID
 * @property int $IdPartner
 * @property int $IdAct
 * @property int $NumSchet
 * @property int $SumSchet
 * @property int $DateSchet
 * @property string $Komment
 * @property int $IsDeleted
 */
class ActSchet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'act_schet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdPartner', 'IdAct', 'SumSchet', 'DateSchet'], 'required'],
            [['Komment'], 'string', 'max' => 255],
            [['IdPartner', 'IdAct', 'NumSchet', 'SumSchet', 'DateSchet', 'IsDeleted'], 'integer'],
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
            'IdAct' => 'Id Act',
            'NumSchet' => 'Num Schet',
            'SumSchet' => 'Sum Schet',
            'DateSchet' => 'Date Schet',
            'Komment' => 'Komment',
            'IsDeleted' => 'Is Deleted',
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    public function GetNextNum()
    {
        $id = Yii::$app->db->createCommand('
            SELECT
                `NumSchet`
            FROM
                `act_schet`
            ORDER BY `ID` DESC 
            LIMIT 1
        ')->queryScalar();
        return (int)$id + 1;
    }

}
