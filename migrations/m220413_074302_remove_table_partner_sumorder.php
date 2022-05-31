<?php

use yii\db\Migration;

/**
 * Class m220413_074302_remove_table_partner_sumorder
 */
class m220413_074302_remove_table_partner_sumorder extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $temp = $this->getDb()->getTableSchema('partner_sumorder');
        if ($temp) {
            $this->dropTable('partner_sumorder');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220413_074302_remove_table_partner_sumorder cannot be reverted.\n";

        return true;
    }
}
