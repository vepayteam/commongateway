<?php

use yii\db\Migration;

/**
 * Class m200206_143957_createindexaftransaction
 */
class m200206_143957_createindexaftransaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('transaction_id_idx', 'antifraud_finger_print','transaction_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200206_143957_createindexaftransaction cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200206_143957_createindexaftransaction cannot be reverted.\n";

        return false;
    }
    */
}
