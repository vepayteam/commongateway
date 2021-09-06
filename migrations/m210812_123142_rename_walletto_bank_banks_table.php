<?php

use yii\db\Migration;

/**
 * Class m210812_123142_rename_walletto_bank_banks_table
 */
class m210812_123142_rename_walletto_bank_banks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('banks', ['Name' => 'Walletto', 'ChannelName' => 'Walletto'], ['ID' => 10]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('banks', ['Name' => 'Walleto', 'ChannelName' => 'Walleto'], ['ID' => 10]);
    }
}
