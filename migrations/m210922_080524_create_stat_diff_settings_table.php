<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stat_diff_settings}}`.
 */
class m210922_080524_create_stat_diff_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('stat_diff_settings', [
            'Id' => $this->primaryKey(),
            'BankId' => $this->integer()->unsigned()->unique(),
            'RegistrySelectColumn' => $this->integer(),
            'RegistryStatusColumn' => $this->integer(),
            'AllRegistryStatusSuccess' => $this->boolean(),
            'DbColumn' => $this->string(),
            'Statuses' => $this->json(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('stat_diff_settings');
    }
}
