<?php

use yii\db\Migration;

/**
 * Class m200505_095852_useremail
 */
class m200505_095852_useremail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet', 'UserEmail', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet', 'UserEmail');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200505_095852_useremail cannot be reverted.\n";

        return false;
    }
    */
}
