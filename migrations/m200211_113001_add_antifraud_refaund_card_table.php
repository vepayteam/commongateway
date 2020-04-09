<?php

use yii\db\Migration;

/**
 * Class m200211_113001_add_antifraud_refaund_card_table
 */
class m200211_113001_add_antifraud_refaund_card_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('antifraud_refund_card', [
            'id'=>$this->primaryKey(),
            'validated'=>$this->boolean(),
            'card_hash'=>$this->string(),
            'partner_id'=>$this->integer(),
            'ext_id'=>$this->string()
        ]);

        $this->createIndex('card_hash', 'antifraud_refund_card', 'card_hash');
        $this->createIndex('partner_id', 'antifraud_refund_card', 'partner_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('antifraud_refund_card');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200211_113001_add_antifraud_refaund_card_table cannot be reverted.\n";

        return false;
    }
    */
}
