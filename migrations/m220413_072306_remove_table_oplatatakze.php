<?php

use yii\db\Migration;

/**
 * Class m220413_072306_remove_table_oplatatakze
 */
class m220413_072306_remove_table_oplatatakze extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $temp = $this->getDb()->getTableSchema('oplatatakze');
        if ($temp) {
            $this->dropTable('oplatatakze');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220413_072306_remove_table_oplatatakze cannot be reverted.\n";

        return true;
    }
}