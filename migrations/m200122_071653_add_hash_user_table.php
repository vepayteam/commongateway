<?php

use yii\db\Migration;

/**
 * Class m200122_071653_add_hash_user_table
 */
class m200122_071653_add_hash_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('fingerprint_hashes', [
            'id'=>$this->primaryKey(),
            'user_hash'=>$this->string(),
            'transaction_id'=>$this->string(),
            'transaction_success'=>$this->boolean(),
            'rating'=>$this->float(),
        ]);

        $this->createIndex('user_hash','fingerprint_hashes', 'user_hash');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('fingerprint_hashes');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200122_071653_add_hash_user_table cannot be reverted.\n";

        return false;
    }
    */
}
