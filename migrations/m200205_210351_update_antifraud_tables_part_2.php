<?php

use yii\db\Migration;

/**
 * Class m200205_210351_update_antifraud_tables_part_2
 */
class m200205_210351_update_antifraud_tables_part_2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->dropIndex('user_hash', 'antifraud_country');
        $this->dropColumn('antifraud_country','user_hash');
        $this->renameColumn('antifraud_country', 'transaction_id', 'finger_print_id');
        $this->alterColumn('antifraud_country', 'finger_print_id', $this->integer());
        $this->createIndex('finger_print_id', 'antifraud_country', 'finger_print_id');

        //$this->dropIndex('id_hash', 'antifraud_cards');
        $this->dropColumn('antifraud_cards', 'user_hash');
        $this->renameColumn('antifraud_cards', 'transaction_id', 'finger_print_id');
        $this->alterColumn('antifraud_cards','finger_print_id', $this->integer());
        $this->createIndex('finger_print_id', 'antifraud_cards', 'finger_print_id');

        $this->alterColumn('antifraud_card_ips', 'transaction_id', $this->integer());
        $this->renameColumn('antifraud_card_ips', 'transaction_id', 'finger_print_id');
        $this->createIndex('finger_print_id', 'antifraud_card_ips', 'finger_print_id');
        $this->createIndex('ip_num', 'antifraud_card_ips', 'ip_num');
        $this->createIndex('card_hash', 'antifraud_card_ips', 'card_hash');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('finger_print_id', 'antifraud_country');
        $this->addColumn('antifraud_country', 'user_hash', $this->string());
        $this->renameColumn('antifraud_country', 'finger_print_id', 'transaction_id');
        $this->alterColumn('antifraud_country', 'transaction_id', $this->string());
        $this->createIndex('user_hash', 'antifraud_country', 'user_hash');

        $this->dropIndex('finger_print_id', 'antifraud_cards');
        $this->addColumn('antifraud_cards', 'user_hash', $this->string());
        $this->renameColumn('antifraud_cards', 'finger_print_id', 'transaction_id');
        $this->alterColumn('antifraud_cards','transaction_id', $this->string());
        $this->createIndex('id_hash', 'antifraud_cards', 'user_hash');


        $this->renameColumn('antifraud_card_ips', 'finger_print_id', 'transaction_id');
        $this->alterColumn('antifraud_card_ips', 'transaction_id', $this->string());
        $this->dropIndex('finger_print_id', 'antifraud_card_ips');
        $this->dropIndex('ip_num', 'antifraud_card_ips');
        $this->dropIndex('card_hash', 'antifraud_card_ips');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200205_210351_update_antifraud_tables_part_2 cannot be reverted.\n";

        return false;
    }
    */
}
