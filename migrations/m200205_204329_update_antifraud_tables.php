<?php

use yii\db\Migration;

/**
 * Class m200205_204329_update_antifraud_tables
 */
class m200205_204329_update_antifraud_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('antifraud_hashes', 'antifraud_finger_print');
        $this->alterColumn('antifraud_finger_print', 'transaction_id', $this->integer());
        $this->renameColumn('antifraud_finger_print', 'transaction_success','status');

        $this->alterColumn('antifraud_stat', 'transaction_id', $this->integer());
        $this->renameColumn('antifraud_stat', 'transaction_id', 'finger_print_id');
        $this->createIndex('finger_print_id', 'antifraud_stat', 'finger_print_id');

        $this->renameColumn('antifraud_ip', 'transaction_id', 'finger_print_id');
        $this->alterColumn('antifraud_ip', 'finger_print_id',$this->integer());
        $this->dropColumn('antifraud_ip','user_hash');
        $this->createIndex('finger_print_id','antifraud_ip', 'finger_print_id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('antifraud_finger_print', 'status', 'transaction_success');
        $this->alterColumn('antifraud_finger_print', 'transaction_id', $this->string());
        $this->renameTable('antifraud_finger_print', 'antifraud_hashes');

        $this->alterColumn('antifraud_stat', 'finger_print_id', $this->string());
        $this->renameColumn('antifraud_stat', 'finger_print_id', 'transaction_id');
        $this->dropIndex('finger_print_id','antifraud_stat');

        $this->renameColumn('antifraud_ip', 'finger_print_id', 'transaction_id');
        $this->alterColumn('antifraud_ip', 'transaction_id',$this->string());
        $this->addColumn('antifraud_ip','user_hash', $this->string());
        $this->dropIndex('finger_print_id', 'antifraud_ip');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200205_204329_update_antifraud_tables cannot be reverted.\n";

        return false;
    }
    */
}
