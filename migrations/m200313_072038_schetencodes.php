<?php

use yii\db\Migration;

/**
 * Class m200313_072038_schetencodes
 */
class m200313_072038_schetencodes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE `act_schet` COLLATE=utf8_general_ci');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200313_072038_schetencodes cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200313_072038_schetencodes cannot be reverted.\n";

        return false;
    }
    */
}
