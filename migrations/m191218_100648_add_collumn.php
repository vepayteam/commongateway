<?php

use yii\db\Migration;

/**
 * Class m191218_100648_add_collumn
 */
class m191218_100648_add_collumn extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet', 'sms_accept', $this->tinyInteger(1)->defaultValue(0));
        $this->createIndex('sms_accept_idx', 'pay_schet', 'sms_accept');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('sms_accept_idx', 'pay_schet');
        $this->dropColumn('pay_schets', 'sms_flag');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191218_100648_add_collumn cannot be reverted.\n";

        return false;
    }
    */
}
