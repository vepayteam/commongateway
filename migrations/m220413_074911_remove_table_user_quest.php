<?php

use yii\db\Migration;

/**
 * Class m220413_074911_remove_table_user_quest
 */
class m220413_074911_remove_table_user_quest extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $temp = $this->getDb()->getTableSchema('user_quest');
        if ($temp) {
            $this->dropTable('user_quest');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220413_074911_remove_table_user_quest cannot be reverted.\n";

        return true;
    }
}
