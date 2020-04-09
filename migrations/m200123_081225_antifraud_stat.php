<?php

use yii\db\Migration;

/**
 * Class m200123_081225_antifraud_stat
 */
class m200123_081225_antifraud_stat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('antifraud_stat', [
            'id'=>$this->primaryKey(),
            'transaction_id'=>$this->string(),
            'rule'=>$this->string(),
            'success'=>$this->boolean()
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('antifraud_stat');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200123_081225_antifraud_stat cannot be reverted.\n";

        return false;
    }
    */
}
