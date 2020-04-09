<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sms}}`.
 */
class m191213_095835_create_sms_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sms}}', [
            'id' => $this->primaryKey(),
            'phone' => $this->string(50),
            'code' => $this->string(50),
            'confirm' => $this->tinyInteger(1),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%sms}}');
    }
}
