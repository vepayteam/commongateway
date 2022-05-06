<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%pay_schgroup}}`.
 */
class m220427_004100_drop_pay_schgroup_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->getTableSchema('pay_schgroup', true)) {
            $this->dropTable('pay_schgroup');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
