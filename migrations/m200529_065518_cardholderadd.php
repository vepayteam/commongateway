<?php

use yii\db\Migration;

/**
 * Class m200529_065518_cardholderadd
 */
class m200529_065518_cardholderadd extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pan_token', 'CardHolder', $this->string(100));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pan_token', 'CardHolder');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200529_065518_cardholderadd cannot be reverted.\n";

        return false;
    }
    */
}
