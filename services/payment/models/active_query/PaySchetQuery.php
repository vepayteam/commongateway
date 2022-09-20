<?php

namespace app\services\payment\models\active_query;

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
     * {@inheritDoc}
     * @return PaySchet[]|array
     */
    public function all($db = null): array
    {
        return parent::all($db);
    }

    /**
     * {@inheritDoc}
     * @return PaySchet|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function needCheckStatusByRsbcron(): PaySchetQuery
    {
        $alias = $this->getTableNameAndAlias()[1];

        $fixedTimeoutTypes = array_merge(UslugatovarType::outTypes(), UslugatovarType::autoTypes());

        return $this
            ->innerJoinWith([
                'uslugatovar ut',/** @see PaySchet::$uslugatovar */
            ])
            ->andWhere([
                "{$alias}.Status" => PaySchet::STATUS_WAITING,
                "{$alias}.sms_accept" => 1,
            ])
            ->andWhere("{$alias}.DateLastUpdate > :twoWeeksAgo")
            ->andWhere("{$alias}.ExtBillNumber IS NOT NULL")
            ->andWhere([
                'or',
                [
                    'and',
                    ['in', 'ut.IsCustom', $fixedTimeoutTypes],
                    "{$alias}.DateLastUpdate < :fixedTimeout",
                ],
                [
                    'and',
                    ['not in', 'ut.IsCustom', $fixedTimeoutTypes],
                    "{$alias}.DateLastUpdate < :now - {$alias}.TimeElapsed",
                ],
            ])
            ->andWhere(['!=', 'ut.IsCustom', UslugatovarType::TOCARD])
            ->addParams([
                ':now' => Carbon::now()->timestamp,
                ':fixedTimeout' => Carbon::now()->addMinutes(-1)->timestamp,
                ':twoWeeksAgo' => Carbon::now()->addDays(-14)->timestamp,
            ]);
    }
}
