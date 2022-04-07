<?php

namespace app\components\activeQuery;

use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;

interface SoftDeleteQuery extends ActiveQueryInterface
{
    /**
     * Returns the name of the field which flags whether a record is deleted.
     *
     * @return string
     */
    public function getSoftDeleteFlag(): string;

    public function softDeleted(): ActiveQuery;

    public function notSoftDeleted(): ActiveQuery;
}