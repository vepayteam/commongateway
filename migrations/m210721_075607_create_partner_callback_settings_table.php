<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%partner_callback_settings}}`.
 */
class m210721_075607_create_partner_callback_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('partner_callback_settings', [
            'Id' => $this->primaryKey(),
            'PartnerId' => $this->integer()->unsigned()->unique(),
            'SendExtId' => $this->boolean(),
            'SendId' => $this->boolean(),
            'SendSum' => $this->boolean(),
            'SendStatus' => $this->boolean(),
            'SendChannel' => $this->boolean(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('partner_callback_settings');
    }
}
