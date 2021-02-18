<?php


namespace app\models\payonline\active_query;


use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\User;
use yii\db\ActiveQuery;

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

}
