<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%reestr_bankpl}}`.
 */
class m220427_000814_drop_reestr_bankpl_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->getTableSchema('reestr_bankpl', true)) {
            $this->dropTable('reestr_bankpl');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
