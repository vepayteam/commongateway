<?php

namespace app\models\payonline\active_query;

use app\models\payonline\Uslugatovar;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\payonline\Uslugatovar]].
 *
 * @see \app\models\payonline\Uslugatovar
 */
class UslugatovarQuery extends ActiveQuery
{
    
    public function withBetween($dateFrom, $dateTo): UslugatovarQuery
    {
        return $this->andWhere(['between', 'DateAdd', $dateFrom, $dateTo]);
    }
    
    /**
     * {@inheritdoc}
     * @return Uslugatovar[]|array
     */
    public function all($db = null): array
    {
        return parent::all($db);
    }
    
    /**
     * {@inheritdoc}
     * @return Uslugatovar|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
