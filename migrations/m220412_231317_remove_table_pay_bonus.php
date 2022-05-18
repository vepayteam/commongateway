<?php

use yii\db\Migration;

/**
 * Class m220412_231317_remove_table_pay_bonus
 */
class m220412_231317_remove_table_pay_bonus extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $temp = $this->getDb()->getTableSchema('pay_bonus');
        if ($temp) {
            $this->dropTable('pay_bonus');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220412_231317_remove_table_pay_bonus cannot be reverted.\n";

        return true;
    }
}
