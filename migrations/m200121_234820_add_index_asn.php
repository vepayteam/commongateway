<?php

use yii\db\Migration;

/**
 * Class m200121_234820_add_index_asn
 */
class m200121_234820_add_index_asn extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('asn', 'asn', 'asn');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('asn', 'asn');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200121_234820_add_index_asn cannot be reverted.\n";

        return false;
    }
    */
}
