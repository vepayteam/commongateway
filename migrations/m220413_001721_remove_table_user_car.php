<?php

use yii\db\Migration;

/**
 * Class m220413_001721_remove_table_user_car
 */
class m220413_001721_remove_table_user_car extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $temp = $this->getDb()->getTableSchema('user_car');
        if ($temp) {
            $this->dropTable('user_car');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220413_001721_remove_table_user_car cannot be reverted.\n";

        return true;
    }
}
