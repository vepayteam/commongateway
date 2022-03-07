<?php

use yii\db\Migration;

/**
 * Class m220121_092014_alter_sumop_field_in_statements_account_table
 */
class m220121_092014_alter_sumpp_field_in_statements_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('statements_account', 'SummPP', 'bigint unsigned default 0 not null comment \'summa\'');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('statements_account', 'SummPP', 'int unsigned default 0 not null comment \'summa\'');
    }
}
