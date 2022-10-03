<?php

use yii\db\Migration;

/**
 * Class m220920_095432_remove_runa_bank
 */
class m220920_095432_remove_runa_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete('banks', ['ID' => 11]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220920_095432_remove_runa_bank cannot be reverted.\n";

        return true;
    }
}
