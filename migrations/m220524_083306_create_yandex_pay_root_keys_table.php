<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%yandex_pay_root_keys}}`.
 */
class m220524_083306_create_yandex_pay_root_keys_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('yandex_pay_root_keys', [
            'id' => $this->primaryKey(),
            'keyValue' => $this->string(255)->unique(),
            'keyExpiration' => $this->bigInteger()->unsigned(),
            'protocolVersion' => $this->string(10),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('yandex_pay_root_keys');
    }
}
