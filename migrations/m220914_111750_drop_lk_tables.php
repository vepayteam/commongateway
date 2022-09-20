<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%cardactivate}}`.
 */
class m220914_111750_drop_lk_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->getTableSchema('auth_login_token', true)) {
            $this->dropTable('auth_login_token');
        }
        if ($this->db->getTableSchema('auth_logins', true)) {
            $this->dropTable('auth_logins');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
