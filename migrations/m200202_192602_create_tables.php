<?php

use yii\db\Migration;

/**
 * Class m200202_192602_create_tables
 */
class m200202_192602_create_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('antifraud_ip', [
            'id' => $this->primaryKey(),
            'id_hash' => $this->integer(),
            'ip_number' => $this->integer(),
            'is_black' => $this->boolean(),
        ]);
        $this->createIndex('ip_number', 'antifraud_ip', 'ip_number');
        $this->createIndex('id_hash', 'antifraud_ip', 'id_hash');

        $this->createTable('antifraud_cards', [
            'id'=>$this->primaryKey(),
            'card_hash'=>$this->string(),
            'id_hash'=>$this->string(),
            'is_black'=>$this->boolean()
        ]);
        $this->createIndex('card_hash', 'antifraud_cards', 'card_hash');
        $this->createIndex('id_hash', 'antifraud_cards', 'id_hash');

        $this->renameColumn('antifraud_hashes', 'rating', 'weight');

        $this->addColumn('antifraud_stat', 'current_weight', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('antifraud_cards');
        $this->dropTable('antifraud_ip');
        $this->dropColumn('antifraud_stat', 'current_weight');
        $this->renameColumn('antifraud_hashes', 'weight', 'rating');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200202_192602_create_tables cannot be reverted.\n";

        return false;
    }
    */
}
