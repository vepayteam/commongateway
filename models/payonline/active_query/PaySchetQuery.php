<?php

namespace app\models\payonline\active_query;

/**
 * This is the ActiveQuery class for [[\app\services\payment\models\PaySchet]].
 *
 * @see \app\services\payment\models\PaySchet
 */
class PaySchetQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\services\payment\models\PaySchet[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\services\payment\models\PaySchet|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
