<?php

use yii\db\Migration;

/**
 * Class m211229_042723_bank_aft_min_sum
 */
class m211229_042723_bank_aft_min_sum extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('banks', 'AftMinSum', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('banks', 'AftMinSum');
    }
}
