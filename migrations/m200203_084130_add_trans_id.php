<?php

use yii\db\Migration;

/**
 * Class m200203_084130_add_trans_id
 */
class m200203_084130_add_trans_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('antifraud_cards', 'transaction_id',$this->string());
        $this->addColumn('antifraud_country', 'transaction_id', $this->string());
        $this->addColumn('antifraud_ip', 'transaction_id', $this->string());
        $this->dropColumn('antifraud_ip', 'ip_number');
        $this->addColumn('antifraud_ip', 'ip_number', $this->integer(111));
        $this->dropColumn('antifraud_ip', 'user_hash');
        $this->addColumn('antifraud_ip', 'user_hash', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->dropColumn('antifraud_cards', 'transaction_id');
       $this->dropColumn('antifraud_country', 'transaction_id');
       $this->dropColumn('antifraud_ip', 'transaction_id');
        $this->dropColumn('antifraud_ip', 'ip_number');
        $this->addColumn('antifraud_ip', 'ip_number', $this->integer(11));
        $this->dropColumn('antifraud_ip', 'user_hash');
        $this->addColumn('antifraud_ip', 'user_hash', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200203_084130_add_trans_id cannot be reverted.\n";

        return false;
    }
    */
}
