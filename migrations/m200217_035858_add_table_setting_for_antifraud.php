<?php

use yii\db\Migration;

/**
 * Class m200217_035858_add_table_setting_for_antifraud
 */
class m200217_035858_add_table_setting_for_antifraud extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('antifraud_settings', [
            'id' => $this->primaryKey(),
            'key' => $this->string(),
            'value' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('antifraud_settings');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200217_035858_add_table_setting_for_antifraud cannot be reverted.\n";

        return false;
    }
    */
}
