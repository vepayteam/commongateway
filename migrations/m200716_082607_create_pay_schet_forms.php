<?php

use yii\db\Migration;

/**
 * Class m200716_082607_create_pay_schet_forms
 */
class m200716_082607_create_pay_schet_forms extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pay_schet_forms', [
            'Id' => $this->primaryKey(),
            'PayschetId' => $this->integer(),
            'Name' => $this->string(),
            'Regex' => $this->string(),
            'Title' => $this->string(),
            'Value' => $this->string(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('pay_schet_forms');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200716_082607_create_pay_schet_forms cannot be reverted.\n";

        return false;
    }
    */
}
