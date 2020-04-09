<?php

use yii\db\Migration;

/**
 * Class m191225_074325_add_partner_sms_table
 */
class m191225_074325_add_partner_sms_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('access_sms', [
            'id' => $this->primaryKey(),
            'partner_id' => $this->integer(),
            'public_key' => $this->string(),
            'secret_key' => $this->string(),
            'description' => $this->string(),
        ]);
        $this->createIndex('partner_id_idx', 'access_sms', 'partner_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('access_sms');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191225_074325_add_partner_sms_table cannot be reverted.\n";

        return false;
    }
    */
}
