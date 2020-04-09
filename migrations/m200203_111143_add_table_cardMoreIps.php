<?php

use yii\db\Migration;

/**
 * Class m200203_111143_add_table_cardMoreIps
 */
class m200203_111143_add_table_cardMoreIps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('antifraud_card_ips', [
            'id'=>$this->primaryKey(),
            'card_hash'=>$this->string(),
            'ip_num'=>$this->integer(111),
            'transaction_id'=>$this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('antifraud_card_ips');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200203_111143_add_table_cardMoreIps cannot be reverted.\n";

        return false;
    }
    */
}
