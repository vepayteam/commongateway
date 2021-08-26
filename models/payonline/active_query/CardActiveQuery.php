<?php

namespace app\models\payonline\active_query;

use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\User;
use yii\db\ActiveQuery;

/**
 * @see \app\models\payonline\Cards
 */
class CardActiveQuery extends ActiveQuery
{

    /**
     * @param Partner $partner
     * @return CardActiveQuery
     */
    public function withPartner(Partner $partner)
    {
        return $this
            ->innerJoin(User::tableName(), User::tableName() . '.ID = ' . Cards::tableName() . '.IdUser')
            ->where([
                User::tableName() . '.ExtOrg' => $partner->ID,
            ]);
    }
    
    /**
     * @param $dateFrom
     * @param $dateTo
     *
     * @return CardActiveQuery
     */
    public function withBetween($dateFrom, $dateTo): CardActiveQuery
    {
        return $this->andWhere(['between', 'DateAdd', $dateFrom, $dateTo]);
    }

}
