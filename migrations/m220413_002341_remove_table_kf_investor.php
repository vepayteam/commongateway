<?php

use yii\db\Migration;

/**
 * Class m220413_002341_remove_table_kf_investor
 */
class m220413_002341_remove_table_kf_investor extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $temp = $this->getDb()->getTableSchema('kf_investor');
        if ($temp) {
            $this->dropTable('kf_investor');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220413_002341_remove_table_kf_investor cannot be reverted.\n";

        return true;
    }
}
