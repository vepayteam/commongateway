<?php

use yii\db\Migration;

/**
 * Class m200203_015255_add_table_history_trans
 */
class m200203_015255_add_table_history_trans extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('antifraud_trans_history', [
            'id'=>$this->primaryKey(),
            'user_hash'=>$this->string(),
            'country'=>$this->string(),
        ]);

        $this->createIndex('user_hash', 'antifraud_trans_history', 'user_hash');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('antifraud_trans_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200203_015255_add_table_history_trans cannot be reverted.\n";

        return false;
    }
    */
}
