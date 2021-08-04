<?php

use yii\db\Migration;

/**
 * Class m210804_083421_add_trace_id_to_queue_table
 */
class m210804_083421_add_trace_id_to_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('queue', 'trace_id', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('queue', 'trace_id');
    }
}
