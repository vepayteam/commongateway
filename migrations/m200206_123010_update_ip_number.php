<?php

use yii\db\Migration;

/**
 * Class m200206_123010_update_ip_number
 */
class m200206_123010_update_ip_number extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('ip_number', 'antifraud_ip' , 'ip_number');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('ip_number', 'antifraud_ip');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200206_123010_update_ip_number cannot be reverted.\n";

        return false;
    }
    */
}
