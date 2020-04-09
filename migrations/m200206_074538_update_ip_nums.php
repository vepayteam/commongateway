<?php

use yii\db\Migration;

/**
 * Class m200206_074538_update_ip_nums
 */
class m200206_074538_update_ip_nums extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('antifraud_ip', 'ip_number', $this->bigInteger());
        $this->alterColumn('antifraud_card_ips', 'ip_num', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('antifraud_ip', 'ip_number', $this->integer());
        $this->alterColumn('antifraud_card_ips', 'ip_num', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200206_074538_update_ip_nums cannot be reverted.\n";

        return false;
    }
    */
}
