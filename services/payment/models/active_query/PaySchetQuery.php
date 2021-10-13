<?php


namespace app\services\payment\models\active_query;


use app\models\payonline\Uslugatovar;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use Carbon\Carbon;
use yii\db\ActiveQuery;

class PaySchetQuery extends ActiveQuery
{
    /**
     * @return PaySchetQuery
     */
    public function needCheckStatusByRsbcron()
    {
        $searchStartTimestamp = Carbon::now()->addDays(-14)->timestamp;
        return $this
            ->innerJoin(
                Uslugatovar::tableName(),
                Uslugatovar::tableName() . '.ID = ' . PaySchet::tableName() . '.IdUsluga'
            )
            ->where([
                PaySchet::tableName() . '.Status' => PaySchet::STATUS_WAITING,
                PaySchet::tableName() . '.sms_accept' => 1,
            ])
            ->andWhere(['>', PaySchet::tableName() . '.DateLastUpdate', $searchStartTimestamp])
            ->andWhere(PaySchet::tableName() . '.ExtBillNumber IS NOT NULL')
            ->andWhere(sprintf(
                '(%1$s.IsCustom NOT IN (%3$s) AND %2$s.DateLastUpdate < UNIX_TIMESTAMP() - %2$s.TimeElapsed) 
                OR (%1$s.IsCustom IN (%3$s) AND %2$s.DateLastUpdate < %4$s)',
                Uslugatovar::tableName(),
                PaySchet::tableName(),
                implode(', ', array_merge(UslugatovarType::outTypes(), UslugatovarType::autoTypes())),
                Carbon::now()->addMinutes(-1)->timestamp
            ));
    }
}
