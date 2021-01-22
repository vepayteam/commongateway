<?php

namespace app\services\payment\models;

use Carbon\Carbon;
use Yii;

/**
 * This is the model class for table "pay_schet_log".
 *
 * @property int $Id
 * @property int $DateCreate
 * @property int $PaySchetId
 * @property int|null $Status
 * @property string|null $ErrorInfo
 * @property PaySchet $paySchet
 */
class PaySchetLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pay_schet_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['DateCreate', 'PaySchetId'], 'required'],
            [['DateCreate', 'PaySchetId', 'Status'], 'integer'],
            [['ErrorInfo'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'DateCreate' => 'Date Create',
            'PaySchetId' => 'Pay Schet ID',
            'Status' => 'Status',
            'ErrorInfo' => 'Error Info',
        ];
    }

    public function getPaySchet()
    {
        return $this->hasOne(PaySchet::className(), ['ID' => 'PaySchetId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function queryLateUpdatedPaySchets(int $IdOrg, int $delta)
    {
        return self::find()
            ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_log.PaySchetId')
            ->where('pay_schet_log.DateCreate - pay_schet.DateCreate > ' . $delta)
            ->andWhere(['=', 'pay_schet.IdOrg', $IdOrg])
            ->andWhere(['>', 'pay_schet_log.DateCreate', Carbon::now()->startOfDay()->addDays(-1)->timestamp])
            ->andWhere(['<', 'pay_schet_log.DateCreate', Carbon::now()->startOfDay()->timestamp]);

    }
}
