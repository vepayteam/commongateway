<?php

namespace app\components\activeQuery;

use yii\db\ActiveQuery;

/**
 * @mixin ActiveQuery
 * @mixin SoftDeleteQuery
 */
trait SoftDelete
{
    /**
     * @return $this|ActiveQuery
     */
    public function notSoftDeleted(): ActiveQuery
    {
        $this->isDeleted(true);
        return $this;
    }

    /**
     * @return $this|ActiveQuery
     */
    public function softDeleted(): ActiveQuery
    {
        $this->isDeleted(false);
        return $this;
    }

    private function isDeleted(bool $flag)
    {
        $alias = $this->getTableNameAndAlias()[1];
        $this->andWhere([$alias . '.' . $this->getSoftDeleteFlag() => (int)$flag]);
    }
}