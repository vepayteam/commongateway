<?php


namespace app\services\payment\models\active_query;


use app\models\payonline\Uslugatovar;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use Carbon\Carbon;
use yii\db\ActiveQuery;

/**
 * @see PaySchet
 */
class PaySchetQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return PaySchet[]|array
     */
    public function all($db = null): array
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return PaySchet|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

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
                implode(', ', array_merge(UslugatovarType::OUT_TYPES, UslugatovarType::AUTO_TYPES)),
                Carbon::now()->addMinutes(-1)->timestamp
            ));
    }
}
