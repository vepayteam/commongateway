<?php

use yii\db\Migration;

/**
 * Class m200121_233225_add_asn_table
 */
class m200121_233225_add_asn_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('asn', [
            'id'=>$this->primaryKey(),
            'asn'=>$this->string(),
            'provider'=>$this->string(),
            'num_ips'=>$this->integer(),
            'num_fails'=>$this->integer()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('asn');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200121_233225_add_asn_table cannot be reverted.\n";

        return false;
    }
    */
}
