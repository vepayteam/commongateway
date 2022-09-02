<?php

use yii\db\Migration;

/**
 * Class m220829_094734_add_payer_bank
 */
class m220829_094734_add_payler_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('banks', [
            'ID' => 17,
            'Name' => 'Payler',
            'ChannelName' => 'Payler',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('banks', ['ID' => 17]);
    }
}
