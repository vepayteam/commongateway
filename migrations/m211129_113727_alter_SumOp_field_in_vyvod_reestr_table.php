<?php

use yii\db\Migration;

class m211129_113727_alter_SumOp_field_in_vyvod_reestr_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('vyvod_reestr', 'SumOp', 'bigint unsigned default 0 not null comment \'summa\'');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('vyvod_reestr', 'SumOp', 'int unsigned default 0 not null comment \'summa\'');
    }
}
