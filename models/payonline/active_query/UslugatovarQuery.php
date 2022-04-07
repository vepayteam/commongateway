<?php

namespace app\models\payonline\active_query;

use app\components\activeQuery\SoftDeleteQuery;
use app\components\activeQuery\SoftDelete;
use app\models\payonline\Uslugatovar;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\payonline\Uslugatovar]].
 *
 * @see \app\models\payonline\Uslugatovar
 */
class UslugatovarQuery extends ActiveQuery implements SoftDeleteQuery
{
    use SoftDelete;

    /**
     * {@inheritDoc}
     */
    public function getSoftDeleteFlag(): string
    {
        /** {@see Uslugatovar::$IsDeleted} */
        return 'IsDeleted';
    }

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
