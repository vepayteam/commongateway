<?php

use yii\db\Migration;

/**
 * Class m191217_111016_keyuseraccess
 */
class m191217_111016_keyuseraccess extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE `vepay`.`key_users` SET `Key1Admin` = '0' , `Key2Admin` = '0' WHERE `ID` = '1'");
        $this->execute("UPDATE `vepay`.`key_users` SET `Key3Admin` = '0' WHERE `ID` = '2'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("UPDATE `vepay`.`key_users` SET `Key1Admin` = '1' , `Key2Admin` = '1' WHERE `ID` = '1'");
        $this->execute("UPDATE `vepay`.`key_users` SET `Key3Admin` = '1' WHERE `ID` = '2'");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191217_111016_keyuseraccess cannot be reverted.\n";

        return false;
    }
    */
}
