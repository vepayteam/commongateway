<?php

use yii\db\Migration;

/**
 * Class m200724_063509_create_auth_logins
 */
class m200724_063509_create_auth_logins extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('auth_logins', [
            'ID' => $this->primaryKey()->unsigned(),
            'PartnerId' => $this->integer()->defaultValue(0),
            'Email' => $this->string(),
            'Login' => $this->string(),
            'PhoneNumber' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('auth_logins');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200724_063509_create_auth_logins cannot be reverted.\n";

        return false;
    }
    */
}
