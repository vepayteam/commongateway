<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%cardactivate}}`.
 */
class m220427_003050_drop_cardactivate_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->getTableSchema('cardactivate', true)) {
            $this->dropTable('cardactivate');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
