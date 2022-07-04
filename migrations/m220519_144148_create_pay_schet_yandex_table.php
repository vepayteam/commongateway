<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pay_schet_yandex}}`.
 */
class m220519_144148_create_pay_schet_yandex_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pay_schet_yandex', [
            'id' => $this->primaryKey(),
            'paySchetId' => $this->integer()->unsigned(),
            'messageId' => $this->string(255),
            'decryptedMessage' => $this->text(),
        ]);

        $this->addForeignKey(
            'fk-pay_schet_yandex-paySchetId',
            'pay_schet_yandex',
            'paySchetId',
            'pay_schet',
            'ID',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pay_schet_yandex-paySchetId', 'pay_schet_yandex');
        $this->dropTable('pay_schet_yandex');
    }
}
