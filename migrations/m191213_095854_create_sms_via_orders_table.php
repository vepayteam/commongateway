<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sms_via_orders}}`.
 */
class m191213_095854_create_sms_via_orders_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sms_via_orders}}', [
            'id' => $this->primaryKey(),
            'sms_id' => $this->integer(),
            'order_id' => $this->integer()
        ]);
        $this->createIndex('sms_id_idx', 'sms_via_orders', 'sms_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%sms_via_orders}}');
    }
}
