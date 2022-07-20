<?php

use yii\db\Migration;

/**
 * Class m220629_112528_add_paylonium_bank
 */
class m220629_112528_add_paylonium_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('banks', [
            'ID' => 16,
            'Name' => 'Paylonium',
            'ChannelName' => 'Paylonium',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('banks', ['ID' => 16]);
    }
}
