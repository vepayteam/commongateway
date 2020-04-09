<?php

use yii\db\Migration;

/**
 * Class m200109_122635_options
 */
class m200109_122635_options extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE `options`(
                `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT, 
                `Name` VARCHAR(255) COMMENT 'opcia', 
                `Value` VARCHAR(255) COMMENT 'znachenie', 
                PRIMARY KEY (`ID`), 
                UNIQUE INDEX (`Name`) 
        )");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('options');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200109_122635_options cannot be reverted.\n";

        return false;
    }
    */
}
