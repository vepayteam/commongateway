<?php

use yii\db\Migration;

/**
 * Class m200130_110852_updvypiskacache
 */
class m200130_110852_updvypiskacache extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE `statements_account` 
            CHANGE `Description` `Description` VARCHAR(500) CHARSET utf8 COLLATE utf8_general_ci NULL COMMENT 'naznachenie'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200130_110852_updvypiskacache cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200130_110852_updvypiskacache cannot be reverted.\n";

        return false;
    }
    */
}
