<?php

use yii\db\Migration;

/**
 * Class m220413_003109_remove_table_kf_orders
 */
class m220413_003109_remove_table_kf_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $temp = $this->getDb()->getTableSchema('kf_orders');
        if ($temp) {
            $this->dropTable('kf_orders');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220413_003109_remove_table_kf_orders cannot be reverted.\n";

        return true;
    }
}
