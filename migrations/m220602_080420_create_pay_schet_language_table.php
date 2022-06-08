<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pay_schet_language}}`.
 */
class m220602_080420_create_pay_schet_language_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pay_schet_language', [
            'id' => $this->primaryKey(),
            'paySchetId' => $this->integer()->unsigned(),
            'apiLanguage' => $this->string(3),
        ]);

        $this->addForeignKey(
            'pay_schet_language_pay_schet_id_fk',
            'pay_schet_language', 'paySchetId',
            'pay_schet', 'ID',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('pay_schet_language_pay_schet_id_fk', 'pay_schet_language`');
        $this->dropTable('pay_schet_language');
    }
}
