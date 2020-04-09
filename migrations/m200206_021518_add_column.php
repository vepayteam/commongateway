<?php

use yii\db\Migration;

/**
 * Class m200206_021518_add_column
 */
class m200206_021518_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('antifraud_country', 'user_hash', $this->string());
        $this->createIndex('user_hash', 'antifraud_country', 'user_hash');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('antifraud_country', 'user_hash');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200206_021518_add_column cannot be reverted.\n";

        return false;
    }
    */
}
